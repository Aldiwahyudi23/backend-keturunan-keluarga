<?php

namespace App\Services;

use App\Models\Person;
use Carbon\Carbon;

class FamilyTreeSearchService
{
    /**
     * Search person by keyword (name, nickname, or person_code)
     */
    public function searchPerson(string $keyword): array
    {
        $keyword = trim($keyword);

        if ($keyword === '') {
            return [];
        }

        return Person::query()
            ->where(function ($q) use ($keyword) {
                $q->where('person_code', 'LIKE', "%{$keyword}%")
                  ->orWhere('full_name', 'LIKE', "%{$keyword}%")
                  ->orWhere('nickname', 'LIKE', "%{$keyword}%");
            })
            ->orderBy('full_name')
            ->limit(50)
            ->get()
            ->map(fn (Person $p) => $this->formatPersonPublic($p))
            ->values()
            ->toArray();
    }

    /**
     * Format untuk data PUBLIK (search & tree)
     */
    private function formatPersonPublic(Person $person): array
    {
        return [
            'id'                 => $person->id,
            'uuid'               => $person->uuid,
            'full_name'          => $person->full_name,
            'nickname'           => $person->nickname,
            'gender'             => $this->genderLabel($person->gender),
            'birth_year'         => $person->birth_date ? Carbon::parse($person->birth_date)->year : null,
            'death_date'         => optional($person->death_date)->format('Y-m-d'),
            'age'                => $this->calculateAge($person),
            'is_deceased'        => !is_null($person->death_date),
            'birth_place'        => $person->birth_place,
            'photo_path'         => $person->photo_path,
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
}