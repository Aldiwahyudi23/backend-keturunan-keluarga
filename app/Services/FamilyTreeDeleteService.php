<?php

namespace App\Services;

use App\Models\Marriage;
use App\Models\ParentChildRelation;
use App\Models\Person;
use Illuminate\Support\Facades\DB;

class FamilyTreeDeleteService
{
    /**
     * Hapus relasi marriage (pernikahan)
     */
    public function deleteMarriage(int $marriageId): array
    {
        $marriage = Marriage::with(['husband', 'wife'])->findOrFail($marriageId);

        $marriageData = [
            'marriage_id' => $marriage->id,
            'husband' => $this->formatPersonCompact($marriage->husband),
            'wife' => $this->formatPersonCompact($marriage->wife),
            'marriage_date' => optional($marriage->marriage_date)->format('Y-m-d'),
            'divorce_date' => optional($marriage->divorce_date)->format('Y-m-d'),
        ];

        $marriage->delete();

        return [
            'deleted_marriage' => $marriageData,
            'message' => 'Data pernikahan berhasil dihapus',
        ];
    }

    /**
     * Hapus relasi anak (melepas anak dari orang tua)
     */
    public function deleteChildRelation(int $parentId, int $childId): array
    {
        $relation = ParentChildRelation::where('parent_id', $parentId)
            ->where('child_id', $childId)
            ->firstOrFail();

        $parent = Person::findOrFail($parentId);
        $child = Person::findOrFail($childId);

        $relation->delete();

        return [
            'parent' => $this->formatPersonCompact($parent),
            'child' => $this->formatPersonCompact($child),
            'message' => 'Relasi anak berhasil dilepas',
        ];
    }

    /**
     * Hapus person dengan semua relasinya
     */
    public function deletePerson(int $personId): array
    {
        $person = Person::findOrFail($personId);

        return DB::transaction(function () use ($person) {
            $personData = $this->formatPersonCompact($person);

            // Kumpulkan data relasi untuk response
            $relations = $this->getRelationsData($person->id);

            // Hapus semua relasi
            Marriage::where('husband_id', $person->id)
                ->orWhere('wife_id', $person->id)
                ->delete();

            ParentChildRelation::where('parent_id', $person->id)
                ->orWhere('child_id', $person->id)
                ->delete();

            $person->delete();

            return [
                'deleted_person' => $personData,
                'deleted_relations' => $relations,
                'message' => 'Person dan semua relasi terkait berhasil dihapus permanen',
            ];
        });
    }

    private function getRelationsData(int $personId): array
    {
        $relations = [];

        // Get marriages
        $marriages = Marriage::where('husband_id', $personId)
            ->orWhere('wife_id', $personId)
            ->with(['husband', 'wife'])
            ->get();

        foreach ($marriages as $marriage) {
            $relations['marriages'][] = [
                'marriage_id' => $marriage->id,
                'spouse' => $this->formatPersonCompact(
                    $marriage->husband_id === $personId ? $marriage->wife : $marriage->husband
                ),
            ];
        }

        // Get children
        $children = ParentChildRelation::where('parent_id', $personId)
            ->with('child')
            ->get();

        foreach ($children as $relation) {
            $relations['children'][] = [
                'child' => $this->formatPersonCompact($relation->child),
                'relation_type' => $relation->type,
            ];
        }

        // Get parents
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

    private function formatPersonCompact(Person $person): array
    {
        return [
            'id' => $person->id,
            'uuid' => $person->uuid,
            'full_name' => $person->full_name,
            'gender' => $person->gender,
        ];
    }
}
