<?php

namespace App\Services;

use App\Models\Person;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;

class PersonCardService
{
    /**
     * Preview Card.
     */
    public function generate(Person $person): Response
    {
        $person->loadMissing([
            'fatherRelation.parent',
        ]);

        $url = url('https://keturunan.keluargamahaya.com/family-tree/' . $person->uuid);

        $qrCode = base64_encode(
            QrCode::format('svg')
                ->size(180)
                ->margin(1)
                ->generate($url)
        );

        $data = [
            'person' => [
                'person_code' => $person->person_code,
                'full_name' => $person->full_name,
                'full_name_with_nasab' => $person->full_name_with_nasab,
                'father_name' => $person->father?->full_name,
                'mother_name' => $person->mother?->full_name,
                'marital_status' => $person->hasActiveMarriage()
                    ? 'Menikah'
                    : 'Belum Menikah',
                'nasab' => $person->nasab,
                'birth_date' => $person->birth_date
                    ? $person->birth_date->translatedFormat('F Y')
                    : '-',
                'photo' => $person->photo_path,
                'qr' => $qrCode,
                'qr_url' => $url,
            ],
        ];

        $pdf = Pdf::loadView(
            'pdf.person-card',
            $data
        )->setPaper('a6', 'landscape');

        return $pdf->stream(
            'kartu-' . $person->person_code . '.pdf'
        );
    }

    /**
     * Download Card.
     */
    public function download(Person $person): Response
    {
        $person->loadMissing([
            'fatherRelation.parent',
        ]);

        
        $url = url('https://keturunan.keluargamahaya.com/family-tree/' . $person->uuid);

        $qrCode = base64_encode(
            QrCode::format('svg')
                ->size(180)
                ->margin(1)
                ->generate($url)
        );

        $data = [
            'person' => [
                'person_code' => $person->person_code,
                'full_name' => $person->full_name,
                'full_name_with_nasab' => $person->full_name_with_nasab,
                'father_name' => $person->father?->full_name,
                'mother_name' => $person->mother?->full_name,
                'marital_status' => $person->hasActiveMarriage()
                    ? 'Menikah'
                    : 'Belum Menikah',
                'nasab' => $person->nasab,
                'birth_date' => $person->birth_date
                    ? $person->birth_date->translatedFormat('F Y')
                    : '-',
                'photo' => $person->photo_path,
                'qr' => $qrCode,
                'qr_url' => $url,
            ],
        ];

        $pdf = Pdf::loadView(
            'pdf.person-card',
            $data
        )->setPaper('a6', 'landscape');

        return $pdf->download(
            'kartu-' . $person->person_code . '.pdf'
        );
    }
}