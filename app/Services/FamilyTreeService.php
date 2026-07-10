<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Marriage;
use App\Models\ParentChildRelation;
use App\Models\Person;
use App\Models\PersonHistory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;

class FamilyTreeService
{
    /** Batas level maksimal untuk tree */
    private const MAX_ANCESTOR_LEVEL = 2;

    private const MAX_DESCENDANT_LEVEL = 2;

    /**
     * Get family tree with relationship IDs
     *
     * @param  int  $maxLevel  - level maksimal (default 2, max 5)
     */
    public function getFamilyTree(string $identifier, int $maxLevel = 2): array
    {
        // Batasi maxLevel antara 2-5
        $maxLevel = max(2, min(5, $maxLevel));

        $person = $this->findPersonOrFail($identifier);

        $nodes = [];
        $visited = [];

        $this->collectNodes($person->id, $nodes, $visited, $maxLevel);

        $formattedNodes = [];
        foreach ($nodes as $nodeId => $personModel) {
            $formattedNodes[] = $this->formatNodeWithRelationsAndActions($personModel);
        }

        return [
            'root_id' => $person->id,

            'can_add_self' => $this->isAuthenticated(),

            'book' => $this->getPublishedBookEditions($person),

            'person_histories' => $this->getPersonHistories($person->id),

            'nodes' => $formattedNodes,
        ];
    }

    /**
     * Kumpulkan semua node yang terkait (naik ke atas, turun ke bawah, dan pasangan)
     */
    private function collectNodes(int $personId, array &$nodes, array &$visited, int $maxLevel, int $currentLevel = 0): void
    {
        if ($currentLevel > $maxLevel || isset($visited[$personId])) {
            return;
        }

        $visited[$personId] = true;
        $person = Person::find($personId);

        if (! $person) {
            return;
        }

        $nodes[$personId] = $person;

        // 1. Ambil pasangan (spouse) dari person ini (level yang sama)
        // HANYA pasangan yang masih menikah (belum cerai)
        $spouses = $this->getActiveSpouses($personId);
        foreach ($spouses as $spouse) {
            if (! isset($visited[$spouse['id']])) {
                $this->collectNodes($spouse['id'], $nodes, $visited, $maxLevel, $currentLevel);
            }
        }

        // 2. Naik ke orang tua (level + 1)
        if ($currentLevel < $maxLevel) {
            $parents = $this->getParentsData($personId);
            if ($parents['father']) {
                $this->collectNodes($parents['father']['id'], $nodes, $visited, $maxLevel, $currentLevel + 1);
            }
            if ($parents['mother']) {
                $this->collectNodes($parents['mother']['id'], $nodes, $visited, $maxLevel, $currentLevel + 1);
            }
        }

        // 3. Turun ke anak (level + 1)
        if ($currentLevel < $maxLevel) {
            $children = $this->getChildren($personId);
            foreach ($children as $child) {
                $this->collectNodes($child['id'], $nodes, $visited, $maxLevel, $currentLevel + 1);
            }
        }
    }

    private function formatNodeWithRelationsAndActions(Person $person): array
    {
        $parents = $this->getParentsData($person->id);
        $spouses = $this->getActiveSpouses($person->id); // Hanya pasangan aktif
        $children = $this->getChildren($person->id);
        $isAuthenticated = $this->isAuthenticated();

        return [
            'id' => $person->id,
            'uuid' => $person->uuid,
            'person_code' => $person->person_code,
            'full_name' => $person->full_name,
            'nickname' => $person->nickname,
            'gender' => $person->gender,
            'birth_year' => $person->birth_date ? Carbon::parse($person->birth_date)->year : null,
            'birth_month' => $person->birth_date ? Carbon::parse($person->birth_date)->month : null,
            'death_date' => optional($person->death_date)->format('Y-m-d'),
            'age' => $this->calculateAge($person),
            'is_deceased' => ! is_null($person->death_date),
            'birth_place' => $person->birth_place,
            'photo_path' => $person->photo_path
            ? url(Storage::url($person->photo_path))
            : null,

            'parent_ids' => array_filter([
                $parents['father']['id'] ?? null,
                $parents['mother']['id'] ?? null,
            ]),
            'spouse_ids' => collect($spouses)->pluck('id')->toArray(),
            'child_ids' => collect($children)->pluck('id')->toArray(),

            'actions' => [

                'can_add_spouse' => $isAuthenticated
                    ? $this->canAddSpouse($person)
                    : false,

                'can_add_parent' => $isAuthenticated
                    ? $this->canAddParent($person->id)
                    : false,

                'can_add_child' => $isAuthenticated
                    ? $this->canAddChild($person->id)
                    : false,
            ],
        ];
    }

    /**
     * Get children - Ambil semua anak langsung dari relasi
     * DURUTKAN berdasarkan kolom 'sort' di ParentChildRelation
     */
    private function getChildren(int $personId): array
    {
        $childRelations = ParentChildRelation::where('parent_id', $personId)
            ->orderBy('sort', 'asc') // Urutkan berdasarkan sort
            ->get();

        if ($childRelations->isEmpty()) {
            return [];
        }

        $childIds = $childRelations->pluck('child_id')->unique();

        // Ambil data person dan tambahkan informasi sort
        $children = Person::whereIn('id', $childIds)
            ->get()
            ->keyBy('id');

        // Format dengan urutan sesuai sort
        $result = [];
        foreach ($childRelations as $relation) {
            $child = $children->get($relation->child_id);
            if ($child) {
                $formattedChild = $this->formatPersonCompact($child);
                // Tambahkan informasi sort dan type dari relasi
                $formattedChild['sort'] = $relation->sort ?? 0;
                $formattedChild['relation_type'] = $relation->type;
                $formattedChild['relation_type_label'] = $relation->type_label;
                $result[] = $formattedChild;
            }
        }

        return $result;
    }

    private function getParentsData(int $personId): array
    {
        $parents = ParentChildRelation::where('child_id', $personId)
            ->with('parent')
            ->get()
            ->pluck('parent')
            ->filter()
            ->values();

        $result = [
            'father' => null,
            'mother' => null,
        ];

        foreach ($parents as $parent) {
            $gender = $parent->gender;
            $data = [
                'id' => $parent->id,
                'full_name' => $parent->full_name,
                'gender' => $this->genderLabel($gender),
            ];

            if ($gender === 'Laki-laki' || $gender === 'male') {
                $result['father'] = $data;
            } elseif ($gender === 'Perempuan' || $gender === 'female') {
                $result['mother'] = $data;
            }
        }

        return $result;
    }

    /**
     * Get active spouses - HANYA pasangan yang masih menikah (belum cerai)
     * Laki-laki: bisa memiliki banyak pasangan
     * Perempuan: hanya satu pasangan (otomatis karena hanya akan ada 1 yang aktif)
     */
    private function getActiveSpouses(int $personId): array
    {
        $person = Person::findOrFail($personId);

        // Ambil pernikahan yang aktif (belum cerai)
        if ($person->gender === 'male' || $person->gender === 'Laki-laki') {
            // Laki-laki: bisa multiple wives (poligami)
            $marriages = Marriage::where('husband_id', $personId)
                ->whereNull('divorce_date')
                ->with('wife')
                ->get();
            $others = $marriages->pluck('wife')->filter()->values();
            $map = $marriages->keyBy('wife_id');
        } else {
            // Perempuan: hanya 1 suami aktif
            $marriage = Marriage::where('wife_id', $personId)
                ->whereNull('divorce_date')
                ->with('husband')
                ->first();

            if (! $marriage) {
                return [];
            }

            $others = collect([$marriage->husband])->filter();
            $map = collect([$marriage->husband_id => $marriage]);
        }

        return $others->map(function (Person $other) use ($map, $personId) {
            $marriage = $map->get($other->id);
            if (! $marriage) {
                return null;
            }

            $data = $this->formatPersonCompact($other);
            $data['marriage'] = [
                'marriage_id' => $marriage->id,
                'marriage_date' => optional($marriage->marriage_date)->format('Y-m-d'),
                'divorce_date' => optional($marriage->divorce_date)->format('Y-m-d'),
                'is_divorced' => ! is_null($marriage->divorce_date),
            ];

            // Tambahkan informasi pasangan ini adalah pasangan ke berapa
            // (untuk laki-laki yang poligami)
            if ($personId && (auth()->user()?->gender ?? $personId) === 'male') {
                $spouseCount = Marriage::where('husband_id', $personId)
                    ->whereNull('divorce_date')
                    ->count();
                $data['spouse_number'] = $spouseCount;
            }

            return $data;
        })->filter()->values()->toArray();
    }

    /**
     * Get ALL spouses (termasuk yang sudah cerai) - untuk keperluan tertentu
     */
    private function getAllSpouses(int $personId): array
    {
        $person = Person::findOrFail($personId);

        if ($person->gender === 'male' || $person->gender === 'Laki-laki') {
            $marriages = Marriage::where('husband_id', $personId)->with('wife')->get();
            $others = $marriages->pluck('wife')->filter()->values();
            $map = $marriages->keyBy('wife_id');
        } else {
            $marriages = Marriage::where('wife_id', $personId)->with('husband')->get();
            $others = $marriages->pluck('husband')->filter()->values();
            $map = $marriages->keyBy('husband_id');
        }

        return $others->map(function (Person $other) use ($map) {
            $marriage = $map->get($other->id);
            $data = $this->formatPersonCompact($other);
            $data['marriage'] = [
                'marriage_id' => $marriage->id,
                'marriage_date' => optional($marriage->marriage_date)->format('Y-m-d'),
                'divorce_date' => optional($marriage->divorce_date)->format('Y-m-d'),
                'is_divorced' => ! is_null($marriage->divorce_date),
            ];

            return $data;
        })->values()->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | CAN-ADD RULES
    |--------------------------------------------------------------------------
    */

    /**
     * Cek apakah bisa tambah pasangan:
     * - Usia minimal 17 tahun
     * - Laki-laki: selalu bisa (poligami) jika sudah cukup umur
     * - Perempuan: hanya bisa jika belum punya pasangan aktif (belum cerai) dan cukup umur
     */
    private function canAddSpouse(Person $person): bool
    {
        // Cek umur minimal 17 tahun
        $age = $this->calculateAge($person);
        if ($age !== null && $age < 17) {
            return false;
        }

        // Laki-laki: bisa jika sudah cukup umur (bisa poligami)
        if ($person->gender === 'male' || $person->gender === 'Laki-laki') {
            return true;
        }

        // Perempuan: cek apakah masih punya pasangan aktif
        $spouses = $this->getActiveSpouses($person->id);

        // Jika sudah punya pasangan aktif, tidak bisa tambah
        if (count($spouses) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Cek apakah bisa tambah orang tua:
     * - Cek apakah sudah punya orang tua dengan type biological
     * - Jika sudah punya biological father dan biological mother, return false
     * - Selain itu return true (bisa tambah step parent / adoptive parent)
     *
     * CATATAN: Ini berlaku untuk SEMUA orang, termasuk pasangan (spouse)
     * karena pasangan juga bisa punya orang tua
     */
    private function canAddParent(int $personId): bool
    {
        // Ambil semua relasi orang tua dengan type biological
        $biologicalParents = ParentChildRelation::where('child_id', $personId)
            ->where('type', 'biological')
            ->with('parent')
            ->get()
            ->pluck('parent')
            ->filter();

        $hasBiologicalFather = false;
        $hasBiologicalMother = false;

        foreach ($biologicalParents as $parent) {
            if ($parent->gender === 'male' || $parent->gender === 'Laki-laki') {
                $hasBiologicalFather = true;
            } elseif ($parent->gender === 'female' || $parent->gender === 'Perempuan') {
                $hasBiologicalMother = true;
            }
        }

        // Jika sudah punya biological father AND biological mother, tidak bisa tambah
        // (karena orang tua biologis sudah lengkap)
        if ($hasBiologicalFather && $hasBiologicalMother) {
            return false;
        }

        return true;
    }

    /**
     * Cek apakah bisa tambah anak:
     * - Harus punya minimal 1 pasangan aktif
     */
    private function canAddChild(int $personId): bool
    {
        $spouses = $this->getActiveSpouses($personId);

        // Cek apakah ada pasangan aktif (belum cerai)
        return count($spouses) > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | FORMATTERS
    |--------------------------------------------------------------------------
    */

    private function formatPersonCompact(Person $person): array
    {
        return [
            'id' => $person->id,
            'uuid' => $person->uuid,
            'person_code' => $person->person_code,
            'full_name' => $person->full_name,
            'nickname' => $person->nickname,
            'gender' => $person->gender,
            'birth_date' => optional($person->birth_date)->format('Y-m-d'),
            'death_date' => optional($person->death_date)->format('Y-m-d'),
            'age' => $this->calculateAge($person),
            'is_deceased' => ! is_null($person->death_date),
            'birth_place' => $person->birth_place,
            'photo_path' => $person->photo_path
            ? url(Storage::url($person->photo_path))
            : null,
        ];
    }

    private function getPersonHistories(int $personId): array
    {
        return PersonHistory::query()
            ->where('person_id', $personId)
            ->orderBy('sort')
            ->orderBy('event_date')
            ->get()
            ->map(function (PersonHistory $history) {
                return [
                    'id' => $history->id,
                    'event_date' => optional($history->event_date)->format('Y-m-d'),
                    'title' => $history->title,
                    'description' => $history->description,
                    'location' => $history->location,
                    'sort' => $history->sort,
                ];
            })
            ->values()
            ->toArray();
    }

    private function genderLabel(?string $gender): ?string
    {
        return match ($gender) {
            'male' => 'Laki-laki',
            'female' => 'Perempuan',
            default => null,
        };
    }

    private function calculateAge(Person $person): ?int
    {
        if (! $person->birth_date) {
            return null;
        }

        $end = $person->death_date ? Carbon::parse($person->death_date) : Carbon::now();

        return Carbon::parse($person->birth_date)->diffInYears($end);
    }

    private function findPersonOrFail(string $identifier): Person
    {
        $person = Person::where('uuid', $identifier)
            ->first();

        if (! $person) {
            throw new ModelNotFoundException("Person not found with identifier: {$identifier}");
        }

        return $person;
    }

    private function isAuthenticated(): bool
    {
        return auth()->check();
    }

    /**
     * Helper: Get person with all relationships
     */
    public function getPersonWithRelations(string $identifier): array
    {
        $person = $this->findPersonOrFail($identifier);
        $isAuthenticated = $this->isAuthenticated();

        return [
            'person' => $this->formatNodeWithRelationsAndActions($person),
            'can_edit' => $isAuthenticated,
            'can_delete' => $isAuthenticated,
            'metadata' => [
                'total_spouses' => count($this->getAllSpouses($person->id)),
                'active_spouses' => count($this->getActiveSpouses($person->id)),
                'total_children' => count($this->getChildren($person->id)),
                'has_both_parents' => $this->hasBothParents($person->id),
            ],
        ];
    }

    /**
     * Cek apakah seseorang memiliki kedua orang tua
     */
    private function hasBothParents(int $personId): bool
    {
        $parents = $this->getParentsData($personId);

        return $parents['father'] !== null && $parents['mother'] !== null;
    }

    /**
     * Get family tree statistics
     */
    public function getFamilyTreeStats(string $identifier): array
    {
        $person = $this->findPersonOrFail($identifier);

        $nodes = [];
        $visited = [];
        $this->collectNodes($person->id, $nodes, $visited, 2);

        return [
            'total_members' => count($nodes),
            'total_spouses' => count($this->getAllSpouses($person->id)),
            'total_children' => count($this->getChildren($person->id)),
            'generations' => $this->calculateGenerations($person->id),
        ];
    }

    /**
     * Hitung generasi dalam family tree
     */
    private function calculateGenerations(int $personId): array
    {
        // Cari level tertinggi (ancestors)
        $maxAncestorLevel = 0;
        $this->findMaxAncestorLevel($personId, 0, $maxAncestorLevel);

        // Cari level terendah (descendants)
        $maxDescendantLevel = 0;
        $this->findMaxDescendantLevel($personId, 0, $maxDescendantLevel);

        return [
            'ancestors' => $maxAncestorLevel,
            'descendants' => $maxDescendantLevel,
            'total' => $maxAncestorLevel + $maxDescendantLevel + 1,
        ];
    }

    private function findMaxAncestorLevel(int $personId, int $currentLevel, int &$maxLevel): void
    {
        if ($currentLevel > $maxLevel) {
            $maxLevel = $currentLevel;
        }

        $parents = $this->getParentsData($personId);
        if ($parents['father']) {
            $this->findMaxAncestorLevel($parents['father']['id'], $currentLevel + 1, $maxLevel);
        }
        if ($parents['mother']) {
            $this->findMaxAncestorLevel($parents['mother']['id'], $currentLevel + 1, $maxLevel);
        }
    }

    private function findMaxDescendantLevel(int $personId, int $currentLevel, int &$maxLevel): void
    {
        if ($currentLevel > $maxLevel) {
            $maxLevel = $currentLevel;
        }

        $children = $this->getChildren($personId);
        foreach ($children as $child) {
            $this->findMaxDescendantLevel($child['id'], $currentLevel + 1, $maxLevel);
        }
    }

    /**
     * Mengambil daftar buku yang telah dipublish
     * berdasarkan tokoh utama (root person).
     */
    private function getPublishedBookEditions(Person $person): array
    {
        $books = Book::query()
            ->published()
            ->where('root_person_id', $person->id)
            ->orderByDesc('published_at')
            ->get([
                'id',
                'title',
                'edition',
                'version',
                'published_at',
            ]);

        return [
            'total' => $books->count(),

            'items' => $books->map(function (Book $book) {
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'edition' => $book->edition,
                    'version' => $book->version,
                    'published_at' => optional($book->published_at)->toDateString(),
                ];
            })->values(),
        ];
    }
}
