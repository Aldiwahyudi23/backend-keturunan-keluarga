<?php

namespace App\Services\Card;

use App\Models\Card\Card;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;

class CardPersonService
{
    public function generate(Card $card): Response
    {
        $data = $this->buildData($card);

        $pdf = Pdf::loadView(
            'pdf.card.classic',
            $data
        )->setPaper([0, 0, 242.65, 153.07]);

        return $pdf->stream(
            'Kartu-'.$card->name.'.pdf'
        );
    }

    public function download(Card $card): Response
    {
        $data = $this->buildData($card);

        $pdf = Pdf::loadView(
            'pdf.card.classic',
            $data
        )->setPaper([0, 0, 242.65, 153.07]);

        return $pdf->download(
            'Kartu-'.$card->name.'.pdf'
        );
    }

    protected function buildData(Card $card): array
    {
        $card->loadMissing([
            'cardPeople.person.fatherRelation.parent',
            'cardPeople.person.motherRelation.parent',
            'contacts' => fn ($q) => $q->where('is_active', true)->orderBy('priority', 'desc'),
        ]);

        $persons = [];

        foreach ($card->cardPeople as $cp) {
            $person = $cp->person;
            $url = url('https://keturunan.keluargamahaya.com/family-tree/'.$person->uuid.'?card='.$card->uuid);

            $qrCode = base64_encode(
                QrCode::format('svg')
                    ->size(180)
                    ->margin(1)
                    ->generate($url)
            );

            $photo = $cp->photo_path
                ? $cp->photo_path
                : $person->photo_path;

            $persons[] = [
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
                'photo' => $photo,
                'qr' => $qrCode,
                'qr_url' => $url,
                'address' => $cp->address,
            ];
        }

        return [
            'card' => [
                'uuid' => $card->uuid,
                'name' => $card->name,
                'title' => $card->title ?: 'KELUARGA BESAR',
                'subtitle' => $card->subtitle ?: 'SILSILAH KELUARGA',
                'logo' => $card->logo_path,
                'background' => $card->background_path,
                'note' => $card->note,
            ],
            'persons' => $persons,
            'contacts' => $card->contacts->toArray(),
        ];
    }
}
