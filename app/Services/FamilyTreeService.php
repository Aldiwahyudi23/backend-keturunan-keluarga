<?php

namespace App\Services;

use App\Models\Person;
use App\Models\ParentChildRelation;
use App\Models\Marriage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class FamilyTreeService
{
    /** Batas level maksimal untuk tree */
    private const MAX_ANCESTOR_LEVEL = 2;
    private const MAX_DESCENDANT_LEVEL = 2;

    /**
     * Get family tree with relationship IDs
     * 
     * @param string $identifier
     * @param int $maxLevel - level maksimal (default 2, max 5)
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
        
        if (!$person) {
            return;
        }

        $nodes[$personId] = $person;

        // 1. Ambil pasangan (spouse) dari person ini (level yang sama)
        $spouses = $this->getSpouses($personId);
        foreach ($spouses as $spouse) {
            if (!isset($visited[$spouse['id']])) {
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
        $spouses = $this->getSpouses($person->id);
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
            'is_deceased' => !is_null($person->death_date),
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
     */
    private function getChildren(int $personId): array
    {
        $childIds = ParentChildRelation::where('parent_id', $personId)
            ->pluck('child_id')
            ->unique();

        if ($childIds->isEmpty()) {
            return [];
        }

        return Person::whereIn('id', $childIds)
            ->get()
            ->map(fn (Person $c) => $this->formatPersonCompact($c))
            ->values()
            ->toArray();
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

    private function getSpouses(int $personId): array
    {
        $person = Person::findOrFail($personId);

        if ($person->gender === 'male') {
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
                'marriage_id'    => $marriage->id,
                'marriage_date'  => optional($marriage->marriage_date)->format('Y-m-d'),
                'divorce_date'   => optional($marriage->divorce_date)->format('Y-m-d'),
                'is_divorced'    => !is_null($marriage->divorce_date),
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

        // Laki-laki: bisa jika sudah cukup umur
        if ($person->gender === 'male' || $person->gender === 'Laki-laki') {
            return true;
        }

        // Perempuan: cek apakah masih punya pasangan aktif
        $spouses = $this->getSpouses($person->id);
        foreach ($spouses as $spouse) {
            if (!$spouse['marriage']['is_divorced']) {
                return false;
            }
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
        $spouses = $this->getSpouses($personId);
        
        foreach ($spouses as $spouse) {
            if (!$spouse['marriage']['is_divorced']) {
                return true;
            }
        }
        
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | FORMATTERS
    |--------------------------------------------------------------------------
    */

    private function formatPersonCompact(Person $person): array
    {
        return [
            'id'           => $person->id,
            'uuid'         => $person->uuid,
            'person_code'  => $person->person_code,
            'full_name'    => $person->full_name,
            'nickname'     => $person->nickname,
            'gender'       => $person->gender,
            'birth_date'   => optional($person->birth_date)->format('Y-m-d'),
            'death_date'   => optional($person->death_date)->format('Y-m-d'),
            'age'          => $this->calculateAge($person),
            'is_deceased'  => !is_null($person->death_date),
            'birth_place'  => $person->birth_place,
            'photo_path' => $person->photo_path
            ? url(Storage::url($person->photo_path))
            : null,
        ];
    }

    private function genderLabel(?string $gender): ?string
    {
        return match ($gender) {
            'male'   => 'Laki-laki',
            'female' => 'Perempuan',
            default  => null,
        };
    }

    private function calculateAge(Person $person): ?int
    {
        if (!$person->birth_date) {
            return null;
        }

        $end = $person->death_date ? Carbon::parse($person->death_date) : Carbon::now();

        return Carbon::parse($person->birth_date)->diffInYears($end);
    }

    private function findPersonOrFail(string $identifier): Person
    {
        $person = Person::where('uuid', $identifier)
            ->orWhere('id', $identifier)
            ->first();

        if (!$person) {
            throw new ModelNotFoundException("Person not found with identifier: {$identifier}");
        }

        return $person;
    }

    private function isAuthenticated(): bool
    {
        return auth()->check();
    }
}