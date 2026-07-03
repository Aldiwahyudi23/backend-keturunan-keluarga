<?php

namespace App\Services\Report;

use App\Models\Person;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class HistoryService
{
    /**
     * Mengambil seluruh sejarah berdasarkan person_id.
     *
     * @param int $personId
     * @return array
     */
    public function generate(int $personId): array
    {
        $person = Person::with([
            'histories' => function ($query) {
                $query
                    ->orderBy('sort')
                    ->orderBy('event_date');
            }
        ])->find($personId);

        if (!$person) {
            return [
                'success' => false,
                'message' => 'Person tidak ditemukan.',
                'data' => [],
            ];
        }

        return [
            'success' => true,
            'message' => 'History berhasil diambil.',
            'data' => [
                'person' => [
                    'id' => $person->id,
                    'person_code' => $person->person_code,
                    'full_name' => $person->full_name,
                    'full_name_with_nasab' => $person->full_name_with_nasab ?? $person->full_name,
                    'bio' => $person->bio,
                    'photo_path' => $person->photo_path,
                    
                ],
                'histories' => $this->transformHistories(
                    $person->histories
                ),
            ],
        ];
    }

    /**
     * Transform collection histories.
     *
     * @param Collection $histories
     * @return array
     */
    private function transformHistories(Collection $histories): array
    {
        return $histories->map(function ($history) {

            return [
                'id' => $history->id,

                'title' => $history->title,

                // HTML dari editor tetap dipertahankan
                'description' => $history->description,

                'location' => $history->location,

                'sort' => $history->sort,

                'event_date' => $history->event_date,

                'event_date_formatted' => $history->event_date
                    ? $this->formatDate($history->event_date)
                    : null,
            ];

        })->values()->toArray();
    }

    /**
     * Format tanggal Indonesia.
     */
    private function formatDate($date): string
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $date->format('d') . ' ' .
            $months[(int) $date->format('m')] . ' ' .
            $date->format('Y');
    }
}