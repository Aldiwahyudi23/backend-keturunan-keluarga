<?php

namespace App\Services\Report;

use App\Models\Person;
use Illuminate\Support\Collection;

class GenealogyService
{
    /**
     * Generate keturunan berdasarkan UUID person
     * 
     * @param string $uuid UUID dari person
     * @param int $maxGenerations Maksimal generasi yang di-fetch (default: unlimited)
     * @return array
     */
    public function generateGeneology(string $uuid, int $maxGenerations = 0): array
    {
        $person = Person::where('uuid', $uuid)->first();

        if (!$person) {
            return [
                'success' => false,
                'message' => 'Person tidak ditemukan',
                'data' => null,
            ];
        }

        // Kumpulkan semua generasi
        $allGenerations = $this->collectGenerations($person, 1, $maxGenerations);

        // Format generasi sesuai yang diinginkan
        $generations = [];
        foreach ($allGenerations as $generationNumber => $members) {
            $generationKey = "Generasi " . $this->convertNumberToRoman($generationNumber);
            $generations[$generationKey] = $members;
        }

        return [
            'success' => true,
            'message' => 'Genealogy berhasil di-generate',
            'data' => [
                'root_person' => $this->buildRootPersonData($person),
                'generations' => $generations,
            ],
        ];
    }

    /**
     * Kumpulkan semua generasi secara flat (tidak bersarang)
     * 
     * @param Person $person
     * @param int $generationNumber Nomor generasi (1 = anak, 2 = cucu, dst)
     * @param int $maxGenerations Maksimal generasi (0 = unlimited)
     * @param int $counter Counter untuk penomoran dalam generasi
     * @return array
     */
    private function collectGenerations(Person $person, int $generationNumber, int $maxGenerations = 0, int &$counter = 0): array
    {
        $result = [];

        // Cek limit generasi
        if ($maxGenerations > 0 && $generationNumber > $maxGenerations) {
            return $result;
        }

        // Ambil children
        $children = $person->children()->get();

        if ($children->isEmpty()) {
            return $result;
        }

        // Inisialisasi counter untuk generasi ini
        $counter = 0;

        // Proses setiap anak
        foreach ($children as $child) {
            $counter++;
            
            // Buat data person dengan numbering berdasarkan generasi
            $childData = $this->buildPersonData($child, $generationNumber . '.' . $counter);
            
            // Tambahkan ke generasi saat ini
            $result[$generationNumber][] = $childData;
        }

        // Proses generasi berikutnya dari semua anak
        $nextGenerations = [];
        foreach ($children as $child) {
            // Reset counter untuk setiap branch
            $nextCounter = 0;
            $childGenerations = $this->collectGenerations($child, $generationNumber + 1, $maxGenerations, $nextCounter);
            
            // Merge hasil generasi berikutnya
            foreach ($childGenerations as $genNum => $members) {
                if (!isset($nextGenerations[$genNum])) {
                    $nextGenerations[$genNum] = [];
                }
                $nextGenerations[$genNum] = array_merge($nextGenerations[$genNum], $members);
            }
        }

        // Merge dengan hasil generasi berikutnya
        foreach ($nextGenerations as $genNum => $members) {
            if (!isset($result[$genNum])) {
                $result[$genNum] = [];
            }
            $result[$genNum] = array_merge($result[$genNum], $members);
        }

        return $result;
    }

    /**
     * Build data root person dengan info lengkap
     * 
     * @param Person $person
     * @return array
     */
    private function buildRootPersonData(Person $person): array
    {
        $spouse = $person->activeSpouse;
        $marriages = $person->gender === 'male' 
            ? $person->marriagesAsHusband()->first()
            : $person->marriagesAsWife()->first();

        $data = [
            'person_code' => $person->person_code,
            'full_name_with_nasab' => $person->full_name_with_nasab ?? $person->full_name,
            'gender' => $this->convertGenderToIndonesian($person->gender),
            'birth_date' => $person->birth_date ? $this->formatDateToMonthYear($person->birth_date) : '-',
            'death_date' => $person->death_date ? $this->formatDateToMonthYear($person->death_date) : '-',
            'spouse' => null,
            'marriage_info' => null,
        ];

        // Tambahkan info pasangan
        if ($spouse) {
            $data['spouse'] = [
                'person_code' => $spouse->person_code,
                'full_name' => $spouse->full_name,
            ];
        }

        // Tambahkan info pernikahan
        if ($marriages) {
            $data['marriage_info'] = [
                'marriage_date' => $marriages->marriage_date ? $this->formatDateToMonthYear($marriages->marriage_date) : '-',
                'divorce_date' => $marriages->divorce_date ? $this->formatDateToMonthYear($marriages->divorce_date) : '-',
                'status' => $marriages->divorce_date ? 'Cerai' : 'Menikah',
            ];
        }

        return $data;
    }

    /**
     * Build data seorang person dengan info lengkap
     * 
     * @param Person $person
     * @param string $numbering
     * @return array
     */
    private function buildPersonData(Person $person, string $numbering): array
    {
        $spouse = $person->activeSpouse;
        $marriages = $person->gender === 'male' 
            ? $person->marriagesAsHusband()->first()
            : $person->marriagesAsWife()->first();

        $children = $person->children()->get();

        $data = [
            'number' => $numbering,
            'person_code' => $person->person_code,
            'full_name_with_nasab' => $person->full_name_with_nasab ?? $person->full_name,
            'gender' => $this->convertGenderToIndonesian($person->gender),
            'birth_date' => $person->birth_date ? $this->formatDateToMonthYear($person->birth_date) : '-',
            'death_date' => $person->death_date ? $this->formatDateToMonthYear($person->death_date) : '-',
            'spouse' => null,
            'marriage_info' => null,
            'children_count' => $children->count(),
            'children' => [],
        ];

        // Tambahkan info pasangan
        if ($spouse) {
            $data['spouse'] = [
                'person_code' => $spouse->person_code,
                'full_name' => $spouse->full_name,
            ];
        }

        // Tambahkan info pernikahan
        if ($marriages) {
            $data['marriage_info'] = [
                'marriage_date' => $marriages->marriage_date ? $this->formatDateToMonthYear($marriages->marriage_date) : '-',
                'divorce_date' => $marriages->divorce_date ? $this->formatDateToMonthYear($marriages->divorce_date) : '-',
                'status' => $marriages->divorce_date ? 'Cerai' : 'Menikah',
            ];
        }

        // List anak (hanya nama untuk display summary)
        if ($children->isNotEmpty()) {
            foreach ($children as $index => $child) {
                $data['children'][] = [
                    'order' => $index + 1,
                    'full_name' => $child->full_name,
                ];
            }
        }

        return $data;
    }

    /**
     * Konversi angka ke angka Romawi
     * 
     * @param int $num
     * @return string
     */
    private function convertNumberToRoman(int $num): string
    {
        $romanNumerals = [
            1000 => 'M',
            900 => 'CM',
            500 => 'D',
            400 => 'CD',
            100 => 'C',
            90 => 'XC',
            50 => 'L',
            40 => 'XL',
            10 => 'X',
            9 => 'IX',
            5 => 'V',
            4 => 'IV',
            1 => 'I',
        ];

        $roman = '';

        foreach ($romanNumerals as $value => $numeral) {
            while ($num >= $value) {
                $roman .= $numeral;
                $num -= $value;
            }
        }

        return $roman;
    }

    /**
     * Convert gender ke Bahasa Indonesia
     * 
     * @param string|null $gender
     * @return string
     */
    private function convertGenderToIndonesian(?string $gender): string
    {
        return match ($gender) {
            'male' => 'Laki-laki',
            'female' => 'Perempuan',
            default => '-',
        };
    }

    /**
     * Format date ke format "Bulan Tahun"
     * Contoh: "Desember 1996"
     * 
     * @param \DateTime|\Illuminate\Support\Carbon $date
     * @return string
     */
    private function formatDateToMonthYear($date): string
    {
        // Map bulan ke Bahasa Indonesia
        $monthsIndonesian = [
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

        $month = (int) $date->format('m');
        $year = $date->format('Y');

        return $monthsIndonesian[$month] . ' ' . $year;
    }

    /**
     * Format response menjadi readable text untuk display/PDF
     * 
     * @param array $genealogyData
     * @return string
     */
    public function formatAsText(array $genealogyData): string
    {
        if (!$genealogyData['success']) {
            return $genealogyData['message'];
        }

        $output = '';
        $data = $genealogyData['data'];

        // Header
        $output .= "═══════════════════════════════════════════════════════\n";
        $output .= "SILSILAH KETURUNAN\n";
        $output .= "Mulai dari: " . $data['root_person']['full_name_with_nasab'] . "\n";
        $output .= "═══════════════════════════════════════════════════════\n\n";

        // Generasi-generasi
        foreach ($data['generations'] as $generationName => $members) {
            $output .= "\n" . strtoupper($generationName) . "\n";
            $output .= str_repeat("─", 55) . "\n";

            foreach ($members as $member) {
                $output .= $this->formatMemberAsText($member, 0);
            }
        }

        return $output;
    }

    /**
     * Format seorang member menjadi text
     * 
     * @param array $member
     * @param int $indent
     * @return string
     */
    private function formatMemberAsText(array $member, int $indent = 0): string
    {
        $padding = str_repeat("  ", $indent);
        $output = '';

        // Data utama
        $output .= "{$padding}{$member['number']}. {$member['full_name_with_nasab']}\n";
        $output .= "{$padding}    Lahir      : {$member['birth_date']}\n";
        $output .= "{$padding}    Wafat      : {$member['death_date']}\n";
        $output .= "{$padding}    Jenis Kel. : {$member['gender']}\n";

        // Pasangan
        if ($member['spouse']) {
            $output .= "{$padding}    Pasangan   : {$member['spouse']['full_name']}\n";
        } else {
            $output .= "{$padding}    Pasangan   : -\n";
        }

        // Info pernikahan
        if ($member['marriage_info']) {
            $output .= "{$padding}    Menikah    : {$member['marriage_info']['marriage_date']}\n";
            if ($member['marriage_info']['divorce_date'] !== '-') {
                $output .= "{$padding}    Cerai      : {$member['marriage_info']['divorce_date']}\n";
            }
        }

        // Anak-anak
        if ($member['children_count'] > 0) {
            $output .= "{$padding}\n";
            $output .= "{$padding}    Anak:\n";
            foreach ($member['children'] as $child) {
                $output .= "{$padding}      {$child['order']}. {$child['full_name']}\n";
            }
        }

        $output .= "\n";

        return $output;
    }
}