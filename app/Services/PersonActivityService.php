<?php

namespace App\Services;

use App\Models\Person;
use App\Models\PersonActivity;

class PersonActivityService
{
    public function store(Person $person, array $data): PersonActivity
    {
        return PersonActivity::create([
            'person_id' => $person->id,
            'description' => $data['description'],
            'can_parent_view' => $data['can_parent_view'] ?? true,
            'created_by' => $data['created_by'] ?? null,
        ]);
    }

    public function getByPerson(Person $person): array
    {
        return PersonActivity::where('person_id', $person->id)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }
}
