<?php

namespace App\Services;

use App\Models\Person;

class FamilyRelationshipService
{
    public function find(
        Person $personA,
        Person $personB
    ): array {

        $ancestorsA = $this->getAncestors($personA);

        $ancestorsB = $this->getAncestors($personB);

        $common = $this->findNearestCommonAncestor(
            $ancestorsA,
            $ancestorsB
        );

        if (!$common) {

            return [
                'related' => false,
                'message' => 'Tidak ditemukan hubungan keluarga.'
            ];
        }

        $relationship = $this->detectRelationship(
            $common['level_a'],
            $common['level_b']
        );

        return [
            'related' => true,

            'common_ancestor' => [
                'id' => $common['person']->id,
                'name' => $common['person']->full_name,
            ],

            'relationship' => $relationship,

            'path_a' => $common['path_a'],

            'path_b' => $common['path_b'],

            'story' => $this->buildStory(
                $common
            ),
        ];
    }

    private function getAncestors(
        Person $person,
        int $level = 0,
        array &$result = [],
        array $path = []
    ): array {

        $path[] = $person->full_name;

        $result[$person->id] = [
            'person' => $person,
            'level' => $level,
            'path' => $path
        ];

        foreach ($person->parents as $parent) {

            $this->getAncestors(
                $parent,
                $level + 1,
                $result,
                $path
            );
        }

        return $result;
    }

    private function findNearestCommonAncestor(
        array $a,
        array $b
    ): ?array {

        $nearest = null;

        foreach ($a as $personId => $ancestorA) {

            if (!isset($b[$personId])) {
                continue;
            }

            $ancestorB = $b[$personId];

            $distance =
                $ancestorA['level']
                +
                $ancestorB['level'];

            if (
                !$nearest
                ||
                $distance < $nearest['distance']
            ) {

                $nearest = [

                    'person' =>
                        $ancestorA['person'],

                    'level_a' =>
                        $ancestorA['level'],

                    'level_b' =>
                        $ancestorB['level'],

                    'path_a' =>
                        $ancestorA['path'],

                    'path_b' =>
                        $ancestorB['path'],

                    'distance' =>
                        $distance,
                ];
            }
        }

        return $nearest;
    }

    private function detectRelationship(
        int $levelA,
        int $levelB
    ): string {

        if (
            $levelA === 1
            &&
            $levelB === 1
        ) {
            return 'Saudara Kandung';
        }

        if (
            $levelA === 2
            &&
            $levelB === 2
        ) {
            return 'Sepupu';
        }

        if (
            $levelA === 3
            &&
            $levelB === 3
        ) {
            return 'Sepupu Dua Kali';
        }

        if (
            $levelA === 1
            &&
            $levelB > 1
        ) {
            return 'Paman/Bibi';
        }

        if (
            $levelB === 1
            &&
            $levelA > 1
        ) {
            return 'Keponakan';
        }

        return 'Keluarga';
    }

    private function buildStory(
        array $common
    ): string {

        $ancestor =
            $common['person']->full_name;

        $pathA =
            implode(
                ' → ',
                array_reverse(
                    $common['path_a']
                )
            );

        $pathB =
            implode(
                ' → ',
                array_reverse(
                    $common['path_b']
                )
            );

        return
            "Hubungan ditemukan melalui leluhur yang sama yaitu {$ancestor}. ".
            "Jalur pertama: {$pathA}. ".
            "Sedangkan jalur kedua: {$pathB}. ".
            "Berdasarkan struktur keluarga yang ditemukan, keduanya memiliki hubungan {$this->detectRelationship($common['level_a'],$common['level_b'])}.";
    }
}