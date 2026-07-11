<?php

namespace App\Services;

use App\Models\ParentChildRelation;
use App\Models\PersonActivity;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PersonActivityService
{
    public function store(\App\Models\Person $person, array $data): PersonActivity
    {
        return PersonActivity::create([
            'person_id' => $person->id,
            'description' => $data['description'],
            'can_parent_view' => $data['can_parent_view'] ?? true,
            'created_by' => $data['created_by'] ?? null,
        ]);
    }

    /**
     * Activity yang dapat dilihat oleh orang tua.
     */
    public function getByParent(
        User $user,
        ?int $childId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = 10
    ): array {

        $person = $user->person;

        if (!$person) {
            return [
                'children' => [],
                'selected_child' => null,
                'activities' => null,
            ];
        }

        /**
         * Ambil semua anak
         */
        $children = ParentChildRelation::query()
            ->where('parent_id', $person->id)
            ->with('child:id,full_name,photo_path')
            ->orderBy('sort')
            ->get();

        if ($children->isEmpty()) {

            return [
                'children' => [],
                'selected_child' => null,
                'activities' => null,
            ];

        }

        /**
         * Jika belum memilih anak,
         * gunakan anak pertama.
         */
        $selectedChild = $childId
            ?: $children->first()->child_id;

        /**
         * Pastikan child memang milik parent ini.
         */
        abort_unless(

            $children
                ->pluck('child_id')
                ->contains($selectedChild),

            403,

            'Anda tidak memiliki akses ke data anak ini.'

        );

        /**
         * Query activity.
         */
        $query = PersonActivity::query()

            ->where('person_id', $selectedChild)

            ->where('can_parent_view', true)

            ->with([
                'creator:id,name',
            ])

            ->latest();

        /**
         * Filter tanggal.
         */
        if ($dateFrom) {

            $query->whereDate(
                'created_at',
                '>=',
                $dateFrom
            );

        }

        if ($dateTo) {

            $query->whereDate(
                'created_at',
                '<=',
                $dateTo
            );

        }

        /** @var LengthAwarePaginator $activities */
        $activities = $query->paginate($perPage);

        return [

            'children' => $children->map(function ($item) {

                return [
                    'id' => $item->child->id,
                    'name' => $item->child->full_name,
                    'photo' => $item->child->photo_path,
                ];

            }),

            'selected_child' => $selectedChild,

            'activities' => $activities,

        ];
    }
}