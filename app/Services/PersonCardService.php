<?php

namespace App\Services;

use App\Models\Person;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PersonCardService
{
    /**
     * Generate PDF kartu person
     */
    public function generateCard(Person $person, $download = true)
    {
        // Ambil data orang tua
        $parents = $person->parents()->get();
        $father = $parents->where('gender', 'male')->first();
        $mother = $parents->where('gender', 'female')->first();

        $data = [
            'person' => $person,
            'father' => $father,
            'mother' => $mother,
            'birth_date_formatted' => $person->birth_date ? Carbon::parse($person->birth_date)->translatedFormat('d F Y') : '-',
            'gender_label' => $person->gender === 'male' ? 'Laki-laki' : 'Perempuan',
        ];

        $pdf = Pdf::loadView('pdf.person-card', $data);
        $pdf->setPaper('a6', 'portrait'); // Ukuran kartu A6 (105 x 148 mm)
        
        // Atau ukuran custom seperti kartu identitas
        // $pdf->setPaper([0, 0, 85.6, 53.98], 'portrait'); // Ukuran kartu ATM/KTP

        if ($download) {
            return $pdf->download("kartu-person-{$person->person_code}.pdf");
        }

        return $pdf->stream("kartu-person-{$person->person_code}.pdf");
    }

    /**
     * Generate dan save kartu ke storage
     */
    public function saveCard(Person $person)
    {
        $data = [
            'person' => $person,
            'father' => $person->parents()->where('gender', 'male')->first(),
            'mother' => $person->parents()->where('gender', 'female')->first(),
            'birth_date_formatted' => $person->birth_date ? Carbon::parse($person->birth_date)->translatedFormat('d F Y') : '-',
            'gender_label' => $person->gender === 'male' ? 'Laki-laki' : 'Perempuan',
        ];

        $pdf = Pdf::loadView('pdf.person-card', $data);
        $pdf->setPaper('a6', 'portrait');

        $filename = "kartu-person-{$person->person_code}.pdf";
        $path = "person-cards/{$filename}";
        
        Storage::disk('public')->put($path, $pdf->output());
        
        return Storage::disk('public')->url($path);
    }
}