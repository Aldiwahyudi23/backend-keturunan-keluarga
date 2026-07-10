<?php

namespace App\Services;

use App\Models\Marriage;
use App\Models\ParentChildRelation;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FamilyTreeUpdateService
{
    /**
     * Update data person
     */
    public function updatePerson(int $personId, array $data): array
    {
        $person = Person::findOrFail($personId);

        // Validasi gender tidak boleh diubah jika sudah memiliki relasi
        if (isset($data['gender']) && $data['gender'] !== $person->gender) {
            $hasRelations = $this->hasRelations($personId);
            if ($hasRelations) {
                throw ValidationException::withMessages([
                    'gender' => ['Tidak dapat mengubah gender karena person ini sudah memiliki relasi (pasangan/anak/orang tua).'],
                ]);
            }
        }

        // Format birth_date jika ada
        if (! empty($data['birth_year'])) {
            $month = $data['birth_month'] ?? 1;
            $day = 1;
            $data['birth_date'] = Carbon::createFromDate($data['birth_year'], $month, $day)->format('Y-m-d');
        }

        // Hapus field yang tidak ada di model
        unset($data['birth_year'], $data['birth_month']);

        $person->update($data);

        return [
            'person' => $this->formatPersonCompact($person),
            'message' => 'Data person berhasil diperbarui',
        ];
    }

    /**
     * Delete person beserta semua relasinya
     */
    public function deletePerson(int $personId): array
    {
        $person = Person::findOrFail($personId);

        return DB::transaction(function () use ($person) {
            // Data untuk response sebelum dihapus
            $personData = $this->formatPersonCompact($person);
            $relations = $this->getPersonRelations($person->id);

            // Hapus semua relasi marriage (sebagai husband atau wife)
            Marriage::where('husband_id', $person->id)
                ->orWhere('wife_id', $person->id)
                ->delete();

            // Hapus semua relasi parent-child (sebagai parent atau child)
            ParentChildRelation::where('parent_id', $person->id)
                ->orWhere('child_id', $person->id)
                ->delete();

            // Hapus person
            $person->delete();

            return [
                'deleted_person' => $personData,
                'deleted_relations' => $relations,
                'message' => 'Person dan semua relasi terkait berhasil dihapus permanen',
            ];
        });
    }

    /**
     * Update marriage (hanya tanggal nikah dan cerai)
     */
    public function updateMarriage(int $marriageId, array $data): array
    {
        $marriage = Marriage::findOrFail($marriageId);

        // Load relasi untuk response
        $marriage->load(['husband', 'wife']);

        // Update hanya field yang diizinkan
        $updatableFields = ['marriage_date', 'divorce_date', 'notes'];
        $updateData = array_intersect_key($data, array_flip($updatableFields));

        // Validasi divorce_date harus setelah marriage_date
        if (! empty($updateData['divorce_date']) && ! empty($updateData['marriage_date'])) {
            if (Carbon::parse($updateData['divorce_date']) <= Carbon::parse($updateData['marriage_date'])) {
                throw ValidationException::withMessages([
                    'divorce_date' => ['Tanggal cerai harus setelah tanggal nikah.'],
                ]);
            }
        }

        // Jika hanya divorce_date diisi, cek dengan marriage_date existing
        if (! empty($updateData['divorce_date']) && empty($updateData['marriage_date']) && $marriage->marriage_date) {
            if (Carbon::parse($updateData['divorce_date']) <= Carbon::parse($marriage->marriage_date)) {
                throw ValidationException::withMessages([
                    'divorce_date' => ['Tanggal cerai harus setelah tanggal nikah ('.$marriage->marriage_date->format('Y-m-d').').'],
                ]);
            }
        }

        $marriage->update($updateData);

        return [
            'marriage' => $this->formatMarriage($marriage),
            'message' => 'Data pernikahan berhasil diperbarui',
        ];
    }

    /**
     * Delete marriage
     */
    public function deleteMarriage(int $marriageId): array
    {
        $marriage = Marriage::with(['husband', 'wife'])->findOrFail($marriageId);

        $marriageData = $this->formatMarriage($marriage);

        // Hapus marriage
        $marriage->delete();

        return [
            'deleted_marriage' => $marriageData,
            'message' => 'Data pernikahan berhasil dihapus',
        ];
    }

    /**
     * Get spouse options with marriage data
     * Digunakan untuk form update marriage
     */
    public function getSpouseOptionsForMarriageForm(int $personId): array
    {
        $person = Person::findOrFail($personId);
        $spouses = $this->getSpousesWithMarriage($personId);

        return [
            'person' => $this->formatPersonCompact($person),
            'spouses' => $spouses,
            'total_spouses' => count($spouses),
        ];
    }

    /**
     * Delete child relation (melepas relasi anak dari orang tua)
     */
    public function deleteChildRelation(int $parentId, int $childId): array
    {
        $parent = Person::findOrFail($parentId);
        $child = Person::findOrFail($childId);

        // Cek apakah relasi exists
        $relation = ParentChildRelation::where('parent_id', $parentId)
            ->where('child_id', $childId)
            ->first();

        if (! $relation) {
            throw ValidationException::withMessages([
                'child_id' => ['Relasi antara orang tua dan anak ini tidak ditemukan.'],
            ]);
        }

        // Hapus relasi
        $relation->delete();

        return [
            'parent' => $this->formatPersonCompact($parent),
            'child' => $this->formatPersonCompact($child),
            'message' => 'Relasi anak berhasil dilepas dari orang tua',
        ];
    }

    /**
     * Delete all child relations untuk parent tertentu (hapus semua anak)
     */
    public function deleteAllChildRelations(int $parentId): array
    {
        $parent = Person::findOrFail($parentId);

        $children = ParentChildRelation::where('parent_id', $parentId)
            ->with('child')
            ->get();

        if ($children->isEmpty()) {
            throw ValidationException::withMessages([
                'parent_id' => ['Tidak ada relasi anak yang ditemukan untuk orang tua ini.'],
            ]);
        }

        $deletedChildren = $children->map(function ($relation) {
            return $this->formatPersonCompact($relation->child);
        })->toArray();

        // Hapus semua relasi
        ParentChildRelation::where('parent_id', $parentId)->delete();

        return [
            'parent' => $this->formatPersonCompact($parent),
            'deleted_children' => $deletedChildren,
            'total_deleted' => count($deletedChildren),
            'message' => 'Semua relasi anak berhasil dilepas dari orang tua',
        ];
    }

    /**
     * Get children with parent relation info
     * Untuk form delete child relation
     */
    public function getChildrenWithRelations(int $personId): array
    {
        $person = Person::findOrFail($personId);

        $childRelations = ParentChildRelation::where('parent_id', $personId)
            ->with(['child', 'child.parents'])
            ->get();

        $children = $childRelations->map(function ($relation) {
            $child = $relation->child;
            $data = $this->formatPersonCompact($child);

            // Cek berapa jumlah orang tua yang dimiliki anak ini
            $parentCount = $child->parents()->count();
            $data['parent_count'] = $parentCount;
            $data['has_both_parents'] = $parentCount >= 2;

            // Cek apakah anak ini memiliki relasi dengan parent lain
            $otherParents = $child->parents()
                ->where('people.id', '!=', $relation->parent_id)
                ->get();

            $data['other_parents'] = $otherParents->map(function ($parent) {
                return $this->formatPersonCompact($parent);
            })->toArray();

            // Info relation
            $data['relation_type'] = $relation->type;
            $data['relation_id'] = $relation->id;

            return $data;
        })->toArray();

        return [
            'parent' => $this->formatPersonCompact($person),
            'children' => $children,
            'total_children' => count($children),
        ];
    }

    /**
     * Get spouse options for delete confirmation
     */
    public function getSpouseOptionsForDelete(int $personId): array
    {
        $person = Person::findOrFail($personId);
        $spouses = $this->getSpousesWithMarriage($personId);
        $children = $this->getChildrenWithRelations($personId);

        return [
            'person' => $this->formatPersonCompact($person),
            'spouses' => $spouses,
            'children' => $children['children'] ?? [],
            'has_relations' => ! empty($spouses) || ! empty($children['children']),
            'warning' => 'Menghapus person akan menghapus SEMUA relasi yang terhubung secara permanen.',
        ];
    }

    // ==================== HELPER METHODS ====================

    private function hasRelations(int $personId): bool
    {
        $hasMarriage = Marriage::where('husband_id', $personId)
            ->orWhere('wife_id', $personId)
            ->exists();

        $hasParentChild = ParentChildRelation::where('parent_id', $personId)
            ->orWhere('child_id', $personId)
            ->exists();

        return $hasMarriage || $hasParentChild;
    }

    private function getPersonRelations(int $personId): array
    {
        $relations = [];

        // Get marriages
        $marriages = Marriage::where('husband_id', $personId)
            ->orWhere('wife_id', $personId)
            ->with(['husband', 'wife'])
            ->get();

        foreach ($marriages as $marriage) {
            $relations['marriages'][] = $this->formatMarriage($marriage);
        }

        // Get child relations as parent
        $children = ParentChildRelation::where('parent_id', $personId)
            ->with('child')
            ->get();

        foreach ($children as $relation) {
            $relations['children'][] = [
                'child' => $this->formatPersonCompact($relation->child),
                'relation_type' => $relation->type,
            ];
        }

        // Get parent relations as child
        $parents = ParentChildRelation::where('child_id', $personId)
            ->with('parent')
            ->get();

        foreach ($parents as $relation) {
            $relations['parents'][] = [
                'parent' => $this->formatPersonCompact($relation->parent),
                'relation_type' => $relation->type,
            ];
        }

        return $relations;
    }

    private function getSpousesWithMarriage(int $personId): array
    {
        $person = Person::findOrFail($personId);

        if ($person->gender === 'male') {
            $marriages = Marriage::where('husband_id', $personId)
                ->with('wife')
                ->get();
            $map = $marriages->keyBy('wife_id');
            $others = $marriages->pluck('wife')->filter();
        } else {
            $marriages = Marriage::where('wife_id', $personId)
                ->with('husband')
                ->get();
            $map = $marriages->keyBy('husband_id');
            $others = $marriages->pluck('husband')->filter();
        }

        return $others->map(function (Person $other) use ($map) {
            $marriage = $map->get($other->id);

            return [
                'person' => $this->formatPersonCompact($other),
                'marriage' => [
                    'marriage_id' => $marriage->id,
                    'marriage_date' => optional($marriage->marriage_date)->format('Y-m-d'),
                    'divorce_date' => optional($marriage->divorce_date)->format('Y-m-d'),
                    'is_divorced' => ! is_null($marriage->divorce_date),
                    'notes' => $marriage->notes,
                ],
            ];
        })->values()->toArray();
    }

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
            'bio' => $person->bio,
        ];
    }

    private function formatMarriage(Marriage $marriage): array
    {
        return [
            'marriage_id' => $marriage->id,
            'husband' => $this->formatPersonCompact($marriage->husband),
            'wife' => $this->formatPersonCompact($marriage->wife),
            'marriage_date' => optional($marriage->marriage_date)->format('Y-m-d'),
            'divorce_date' => optional($marriage->divorce_date)->format('Y-m-d'),
            'is_divorced' => ! is_null($marriage->divorce_date),
            'notes' => $marriage->notes,
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
}
