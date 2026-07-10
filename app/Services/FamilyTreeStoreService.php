<?php

namespace App\Services;

use App\Models\Marriage;
use App\Models\ParentChildRelation;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FamilyTreeStoreService
{
    /** Batas usia minimal untuk bisa ditambahkan sebagai pasangan */
    private const MIN_SPOUSE_AGE = 17;

    /*
    |--------------------------------------------------------------------------
    | MAIN: STORE
    |--------------------------------------------------------------------------
    */
    public function addPersonWithRelation(array $data): array
    {
        return DB::transaction(function () use ($data) {

            $source = $data['source'] ?? 'self';

            // Validasi khusus berdasarkan source
            if ($source === 'spouse') {
                $this->validateSpouse($data);
            }

            if ($source === 'parent') {
                $this->validateParent($data);
            }

            if ($source === 'child') {
                $this->validateChild($data);
            }

            // Buat person (atau gunakan existing jika ada selected_person_id)
            $person = $this->createOrGetPerson($data);

            $marriage = null;

            switch ($source) {
                case 'spouse':
                    $marriage = $this->createSpouseRelation($person, (int) $data['related_person_id'], $data);
                    if (! empty($data['child_ids']) && is_array($data['child_ids'])) {
                        $this->connectChildrenToSpouse($person->id, $data['child_ids']);
                    }
                    break;

                case 'child':
                    $this->createChildRelation($person, (int) $data['related_person_id'], $data);
                    if (! empty($data['additional_parent_id'])) {
                        $this->connectChildToAdditionalParent($person->id, (int) $data['additional_parent_id'], $data);
                    }
                    break;

                case 'parent':
                    $marriage = $this->createParentRelation($person, (int) $data['related_person_id'], $data);
                    break;

                case 'self':
                default:
                    break;
            }

            return [
                'person' => $this->formatPersonCompact($person),
                'marriage' => $marriage,
            ];
        });
    }

    /**
     * Buat person baru ATAU ambil dari database jika selected_person_id ada
     */
    private function createOrGetPerson(array $data): Person
    {
        // Jika ada selected_person_id, ambil dari database
        if (! empty($data['selected_person_id'])) {
            $person = Person::find($data['selected_person_id']);
            if ($person) {
                return $person;
            }
        }

        // Buat person baru
        $birthDate = null;
        if (! empty($data['birth_year'])) {
            $month = $data['birth_month'] ?? 1;
            $day = 1;
            $birthDate = Carbon::createFromDate($data['birth_year'], $month, $day)->format('Y-m-d');
        }

        return Person::create([
            'full_name' => $data['full_name'],
            'nickname' => $data['nickname'] ?? null,
            'gender' => $data['gender'],
            'birth_date' => $birthDate,
            'death_date' => $data['death_date'] ?? null,
            'birth_place' => $data['birth_place'] ?? null,
            'photo_path' => $data['photo_path'] ?? null,
            'bio' => $data['bio'] ?? null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATIONS
    |--------------------------------------------------------------------------
    */

    private function validateSpouse(array $data): void
    {
        $related = Person::find($data['related_person_id']);
        if (! $related) {
            throw ValidationException::withMessages([
                'related_person_id' => ['Data orang yang dirujuk tidak ditemukan.'],
            ]);
        }

        if ($related->gender === $data['gender']) {
            throw ValidationException::withMessages([
                'gender' => ['Gender pasangan harus berbeda dengan '.$related->full_name.'.'],
            ]);
        }

        if (! $this->canAddSpouse($related)) {
            throw ValidationException::withMessages([
                'related_person_id' => [$related->full_name.' masih memiliki pasangan aktif, tidak bisa menambah pasangan baru.'],
            ]);
        }

        $this->assertAdultForMarriage($related);
    }

    private function validateParent(array $data): void
    {
        $child = Person::find($data['related_person_id']);
        if (! $child) {
            throw ValidationException::withMessages([
                'related_person_id' => ['Data anak tidak ditemukan.'],
            ]);
        }

        $parentCount = ParentChildRelation::where('child_id', $child->id)->count();
        if ($parentCount >= 2) {
            throw ValidationException::withMessages([
                'related_person_id' => ['Orang tua sudah lengkap (ayah & ibu).'],
            ]);
        }

        $existingParents = $this->getParents($child->id);
        foreach ($existingParents as $existingParent) {
            if ($existingParent['gender'] === $data['gender']) {
                $label = $data['gender'] === 'male' ? 'ayah' : 'ibu';
                throw ValidationException::withMessages([
                    'gender' => ["Sudah ada {$label} untuk {$child->full_name}."],
                ]);
            }
        }
    }

    private function validateChild(array $data): void
    {
        $parent = Person::find($data['related_person_id']);
        if (! $parent) {
            throw ValidationException::withMessages([
                'related_person_id' => ['Data orang tua tidak ditemukan.'],
            ]);
        }

        $spouses = $this->getSpouses($parent->id);
        if (empty($spouses)) {
            throw ValidationException::withMessages([
                'related_person_id' => ["{$parent->full_name} belum memiliki pasangan, tidak bisa menambah anak."],
            ]);
        }

        if (count($spouses) > 1 && empty($data['spouse_id'])) {
            throw ValidationException::withMessages([
                'spouse_id' => ['Orang ini memiliki lebih dari satu pasangan, pilih salah satu sebagai orang tua anak.'],
            ]);
        }

        if (! empty($data['spouse_id'])) {
            $spouseIds = collect($spouses)->pluck('id')->all();
            if (! in_array((int) $data['spouse_id'], $spouseIds, true)) {
                throw ValidationException::withMessages([
                    'spouse_id' => ['spouse_id yang dipilih bukan pasangan yang valid dari related_person_id.'],
                ]);
            }
        }

        if (! empty($data['additional_parent_id'])) {
            $additionalParent = Person::find($data['additional_parent_id']);
            if (! $additionalParent) {
                throw ValidationException::withMessages([
                    'additional_parent_id' => ['Data orang tua tambahan tidak ditemukan.'],
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GET RELATION LISTS
    |--------------------------------------------------------------------------
    */

    public function getSpouses(int $personId): array
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
                'marriage_id' => $marriage->id,
                'marriage_date' => optional($marriage->marriage_date)->format('Y-m-d'),
                'divorce_date' => optional($marriage->divorce_date)->format('Y-m-d'),
                'is_divorced' => ! is_null($marriage->divorce_date),
            ];

            return $data;
        })->values()->toArray();
    }

    public function getParents(int $personId): array
    {
        return ParentChildRelation::where('child_id', $personId)
            ->with('parent')
            ->get()
            ->pluck('parent')
            ->filter()
            ->map(fn (Person $p) => $this->formatPersonCompact($p))
            ->values()
            ->toArray();
    }

    public function getChildren(int $personId): array
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

    public function getSpouseOptionsWithChildren(int $personId): array
    {
        $person = Person::findOrFail($personId);
        $spouses = $this->getSpouses($personId);
        $children = $this->getChildren($personId);

        return [
            'person' => $this->formatPersonCompact($person),
            'spouse_options' => $spouses,
            'children' => $children,
            'requires_spouse_selection' => count($spouses) > 1,
            'can_add_spouse' => $this->canAddSpouse($person),
            'can_add_child' => $this->canAddChild($personId),
        ];
    }

    public function getSpouseOptionsForChildForm(int $personId): array
    {
        $person = Person::findOrFail($personId);
        $spouses = $this->getSpouses($personId);

        return [
            'person' => $this->formatPersonCompact($person),
            'spouse_options' => $spouses,
            'requires_spouse_selection' => count($spouses) > 1,
            'can_add_child' => $this->canAddChild($personId),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CAN-ADD RULES
    |--------------------------------------------------------------------------
    */

    private function canAddSpouse(Person $person): bool
    {
        if ($person->gender === 'male' || $person->gender === 'Laki-laki') {
            return true;
        }

        $spouses = $this->getSpouses($person->id);
        foreach ($spouses as $spouse) {
            if (! $spouse['marriage']['is_divorced']) {
                return false;
            }
        }

        return true;
    }

    private function canAddParent(int $personId): bool
    {
        $parentCount = ParentChildRelation::where('child_id', $personId)->count();

        return $parentCount < 2;
    }

    private function canAddChild(int $personId): bool
    {
        $spouses = $this->getSpouses($personId);

        return ! empty($spouses);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE RELATION HELPERS
    |--------------------------------------------------------------------------
    */

    private function createSpouseRelation(Person $newPerson, int $relatedPersonId, array $data): array
    {
        $related = Person::findOrFail($relatedPersonId);

        if ($related->gender === 'male') {
            $husband = $related;
            $wife = $newPerson;
        } else {
            $husband = $newPerson;
            $wife = $related;
        }

        $alreadyMarried = Marriage::where('husband_id', $husband->id)
            ->where('wife_id', $wife->id)
            ->exists();

        if ($alreadyMarried) {
            throw ValidationException::withMessages([
                'related_person_id' => ['Pasangan ini sudah terdaftar menikah.'],
            ]);
        }

        $marriage = Marriage::create([
            'husband_id' => $husband->id,
            'wife_id' => $wife->id,
            'marriage_date' => $data['marriage_date'] ?? null,
            'divorce_date' => $data['divorce_date'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return $this->formatMarriage($marriage, $husband, $wife);
    }

    private function connectChildrenToSpouse(int $spouseId, array $childIds): void
    {
        foreach ($childIds as $childId) {
            $exists = ParentChildRelation::where('parent_id', $spouseId)
                ->where('child_id', $childId)
                ->exists();

            if (! $exists) {
                ParentChildRelation::create([
                    'parent_id' => $spouseId,
                    'child_id' => $childId,
                    'type' => 'biological',
                ]);
            }
        }
    }

    private function createChildRelation(Person $child, int $parentId, array $data): void
    {
        $spouses = $this->getSpouses($parentId);
        $secondParentId = $data['spouse_id'] ?? ($spouses[0]['id'] ?? null);

        $relationType = $data['relation_type'] ?? 'biological';

        ParentChildRelation::create([
            'parent_id' => $parentId,
            'child_id' => $child->id,
            'type' => $relationType,
        ]);

        if ($secondParentId) {
            ParentChildRelation::create([
                'parent_id' => $secondParentId,
                'child_id' => $child->id,
                'type' => $relationType,
            ]);
        }
    }

    private function connectChildToAdditionalParent(int $childId, int $parentId, array $data): void
    {
        $relationType = $data['relation_type'] ?? 'biological';

        $exists = ParentChildRelation::where('parent_id', $parentId)
            ->where('child_id', $childId)
            ->exists();

        if (! $exists) {
            ParentChildRelation::create([
                'parent_id' => $parentId,
                'child_id' => $childId,
                'type' => $relationType,
            ]);
        }
    }

    private function createParentRelation(Person $newParent, int $childId, array $data): ?array
    {
        $relationType = $data['relation_type'] ?? 'biological';

        ParentChildRelation::create([
            'parent_id' => $newParent->id,
            'child_id' => $childId,
            'type' => $relationType,
        ]);

        $existingParents = ParentChildRelation::where('child_id', $childId)
            ->with('parent')
            ->get()
            ->pluck('parent')
            ->filter();

        $otherParent = $existingParents->first(
            fn (Person $p) => $p->id !== $newParent->id && $p->gender !== $newParent->gender
        );

        if (! $otherParent) {
            return null;
        }

        if ($newParent->gender === 'male') {
            $husband = $newParent;
            $wife = $otherParent;
        } else {
            $husband = $otherParent;
            $wife = $newParent;
        }

        $alreadyMarried = Marriage::where('husband_id', $husband->id)
            ->where('wife_id', $wife->id)
            ->exists();

        if (! $alreadyMarried) {
            $marriage = Marriage::create([
                'husband_id' => $husband->id,
                'wife_id' => $wife->id,
                'marriage_date' => $data['marriage_date'] ?? null,
                'divorce_date' => $data['divorce_date'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            return $this->formatMarriage($marriage, $husband, $wife, true);
        }

        return null;
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
            'photo_path' => $person->photo_path,
        ];
    }

    private function formatMarriage(Marriage $marriage, Person $husband, Person $wife, bool $autoCreated = false): array
    {
        return [
            'marriage_id' => $marriage->id,
            'husband' => $this->formatPersonCompact($husband),
            'wife' => $this->formatPersonCompact($wife),
            'marriage_date' => optional($marriage->marriage_date)->format('Y-m-d'),
            'divorce_date' => optional($marriage->divorce_date)->format('Y-m-d'),
            'is_divorced' => ! is_null($marriage->divorce_date),
            'auto_created' => $autoCreated,
        ];
    }

    private function calculateAge(Person $person): ?int
    {
        if (! $person->birth_date) {
            return null;
        }

        $end = $person->death_date ? Carbon::parse($person->death_date) : Carbon::now();

        return Carbon::parse($person->birth_date)->diffInYears($end);
    }

    private function assertAdultForMarriage(Person $person): void
    {
        if (! $person->birth_date) {
            return;
        }

        $age = Carbon::parse($person->birth_date)->age;

        if ($age < self::MIN_SPOUSE_AGE) {
            throw ValidationException::withMessages([
                'related_person_id' => ["{$person->full_name} berumur {$age} tahun, minimal harus ".self::MIN_SPOUSE_AGE.' tahun untuk ditambahkan sebagai pasangan.'],
            ]);
        }
    }
}
