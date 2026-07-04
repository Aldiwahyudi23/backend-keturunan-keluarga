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
        foreach ($allGenerations as $generationNumber => $generationData) {
            $generationKey = "Generasi " . $this->convertNumberToRoman($generationNumber);
            $generations[$generationKey] = $generationData;
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
     * Kumpulkan semua generasi dengan grouping berdasarkan pasangan
     * 
     * @param Person $person
     * @param int $generationNumber Nomor generasi (1 = anak, 2 = cucu, dst)
     * @param int $maxGenerations Maksimal generasi (0 = unlimited)
     * @return array
     */
    private function collectGenerations(Person $person, int $generationNumber, int $maxGenerations = 0): array
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

        // Group children by parent (pasangan)
        $childrenBySpouse = $this->groupChildrenBySpouse($children);

        // Counter untuk numbering
        $counter = 0;

        // Proses setiap group pasangan
        foreach ($childrenBySpouse as $spouseId => $group) {
            $counter++;
            
            // Ambil data pasangan
            $spouse = $group['spouse'];
            $childrenList = $group['children'];
            
            // Buat parent info
            $husband = null;
            $wife = null;
            
            if ($person->gender === 'male') {
                $husband = $person;
                $wife = $spouse;
            } else {
                $husband = $spouse;
                $wife = $person;
            }
            
            $parentInfo = $this->buildParentInfo($husband, $wife);
            
            // Buat data member untuk setiap anak
            $members = [];
            $childCounter = 0;
            
            foreach ($childrenList as $child) {
                $childCounter++;
                $members[] = $this->buildPersonData($child, $generationNumber . '.' . $childCounter);
            }
            
            // Tambahkan ke generasi saat ini
            $result[$generationNumber][] = [
                'parent_info' => $parentInfo,
                'members' => $members,
            ];
        }

        // Proses generasi berikutnya dari semua anak
        $nextGenerations = [];
        foreach ($children as $child) {
            $childGenerations = $this->collectGenerations($child, $generationNumber + 1, $maxGenerations);
            
            // Merge hasil generasi berikutnya
            foreach ($childGenerations as $genNum => $groups) {
                if (!isset($nextGenerations[$genNum])) {
                    $nextGenerations[$genNum] = [];
                }
                $nextGenerations[$genNum] = array_merge($nextGenerations[$genNum], $groups);
            }
        }

        // Merge dengan hasil generasi berikutnya
        foreach ($nextGenerations as $genNum => $groups) {
            if (!isset($result[$genNum])) {
                $result[$genNum] = [];
            }
            $result[$genNum] = array_merge($result[$genNum], $groups);
        }

        return $result;
    }

    /**
     * Group children by spouse (pasangan)
     * 
     * @param Collection $children
     * @return array
     */
    private function groupChildrenBySpouse(Collection $children): array
    {
        $groups = [];

        foreach ($children as $child) {
            // Cari pasangan dari orang tua anak
            $spouse = $this->findSpouseOfChild($child);
            
            // Gunakan ID pasangan sebagai key, atau 'unknown' jika tidak ditemukan
            $key = $spouse ? $spouse->id : 'unknown';
            
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'spouse' => $spouse,
                    'children' => [],
                ];
            }
            
            $groups[$key]['children'][] = $child;
        }

        return $groups;
    }

    /**
     * Cari pasangan dari orang tua anak (berdasarkan parent-child relation)
     * 
     * @param Person $child
     * @return Person|null
     */
    private function findSpouseOfChild(Person $child): ?Person
    {
        // Ambil semua parent dari anak
        $parents = $child->parents()->get();
        
        if ($parents->count() < 2) {
            return null;
        }
        
        // Cari pasangan (spouse) - ambil parent yang berbeda gender
        foreach ($parents as $parent1) {
            foreach ($parents as $parent2) {
                if ($parent1->id !== $parent2->id && $parent1->gender !== $parent2->gender) {
                    // Cek apakah mereka terikat pernikahan
                    $marriage = $this->findMarriageBetween($parent1, $parent2);
                    if ($marriage) {
                        // Kembalikan pasangan dari parent1
                        return $parent2;
                    }
                }
            }
        }
        
        return null;
    }

    /**
     * Cari pernikahan antara dua orang
     * 
     * @param Person $person1
     * @param Person $person2
     * @return \App\Models\Marriage|null
     */
    private function findMarriageBetween(Person $person1, Person $person2): ?\App\Models\Marriage
    {
        // Cari pernikahan dimana person1 adalah suami dan person2 adalah istri
        $marriage = \App\Models\Marriage::where('husband_id', $person1->id)
            ->where('wife_id', $person2->id)
            ->first();
            
        if ($marriage) {
            return $marriage;
        }
        
        // Cari pernikahan dimana person1 adalah istri dan person2 adalah suami
        $marriage = \App\Models\Marriage::where('husband_id', $person2->id)
            ->where('wife_id', $person1->id)
            ->first();
            
        return $marriage;
    }

    /**
     * Build parent info string
     * 
     * @param Person|null $husband
     * @param Person|null $wife
     * @return string
     */
    private function buildParentInfo(?Person $husband, ?Person $wife): string
    {
        if ($husband && $wife) {
            return "Pasangan {$husband->full_name} dan {$wife->full_name}";
        } elseif ($husband) {
            return "Pasangan {$husband->full_name}";
        } elseif ($wife) {
            return "Pasangan {$wife->full_name}";
        }
        
        return "Pasangan";
    }

    /**
     * Build data root person dengan info lengkap
     * 
     * @param Person $person
     * @return array
     */
    private function buildRootPersonData(Person $person): array
    {
        $marriages = $this->getAllMarriages($person);
        $children = $person->children()->get();
        $totalChildren = $children->count();

        $data = [
            'person_code' => $person->person_code,
            'full_name_with_nasab' => $person->full_name_with_nasab ?? $person->full_name,
            'gender' => $this->convertGenderToIndonesian($person->gender),
            'birth_date' => $person->birth_date ? $this->formatDateToMonthYear($person->birth_date) : '-',
            'death_date' => $person->death_date ? $this->formatDateToMonthYear($person->death_date) : '-',
            'bio' => $person->bio,
            'photo_path' => $person->photo_path,
            'marriage_summary' => $this->buildMarriageSummary($person, $marriages, $totalChildren),
            'marriages' => $this->buildMarriagesData($person, $marriages),
        ];

        return $data;
    }

    /**
     * Get all marriages for a person
     * 
     * @param Person $person
     * @return Collection
     */
    private function getAllMarriages(Person $person): Collection
    {
        if ($person->gender === 'male') {
            return $person->marriagesAsHusband()->with('wife')->get();
        } else {
            return $person->marriagesAsWife()->with('husband')->get();
        }
    }

    /**
     * Build marriage summary text
     * 
     * @param Person $person
     * @param Collection $marriages
     * @param int $totalChildren
     * @return string
     */
    private function buildMarriageSummary(Person $person, Collection $marriages, int $totalChildren): string
    {
        if ($marriages->isEmpty()) {
            return "{$person->full_name} belum menikah.";
        }

        $summary = [];
        $order = 1;

        foreach ($marriages as $marriage) {
            $spouse = $person->gender === 'male' ? $marriage->wife : $marriage->husband;
            $spouseName = $spouse ? $spouse->full_name : 'pasangan';
            
            // Hitung anak dari pernikahan ini
            $childrenFromMarriage = $this->countChildrenFromMarriage($person, $spouse);
            
            $status = $marriage->divorce_date ? 'pernah menikah dengan' : 'menikah dengan';
            
            if ($childrenFromMarriage > 0) {
                $summary[] = "{$person->full_name} {$status} {$spouseName} dan memperoleh {$childrenFromMarriage} orang anak.";
            } else {
                $summary[] = "{$person->full_name} {$status} {$spouseName}.";
            }
            
            $order++;
        }

        return implode(' ', $summary);
    }

    /**
     * Count children from a specific marriage
     * 
     * @param Person $person
     * @param Person|null $spouse
     * @return int
     */
    private function countChildrenFromMarriage(Person $person, ?Person $spouse): int
    {
        if (!$spouse) {
            return 0;
        }

        // Cari anak yang memiliki kedua orang tua ini
        $children = $person->children()->get();
        $count = 0;

        foreach ($children as $child) {
            $parents = $child->parents()->get();
            $parentIds = $parents->pluck('id')->toArray();
            
            if (in_array($person->id, $parentIds) && in_array($spouse->id, $parentIds)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Build marriages data
     * 
     * @param Person $person
     * @param Collection $marriages
     * @return array
     */
    private function buildMarriagesData(Person $person, Collection $marriages): array
    {
        $result = [];
        $order = 1;

        foreach ($marriages as $marriage) {
            $spouse = $person->gender === 'male' ? $marriage->wife : $marriage->husband;
            $childrenCount = $spouse ? $this->countChildrenFromMarriage($person, $spouse) : 0;

            $data = [
                'order' => $order,
                'status' => $marriage->divorce_date ? 'Cerai' : 'Menikah',
                'marriage_date' => $marriage->marriage_date ? $this->formatDateToMonthYear($marriage->marriage_date) : '-',
                'divorce_date' => $marriage->divorce_date ? $this->formatDateToMonthYear($marriage->divorce_date) : '-',
                'children_count' => $childrenCount,
            ];

            if ($spouse) {
                $data['spouse'] = [
                    'person_code' => $spouse->person_code,
                    'full_name' => $spouse->full_name,
                ];
            }

            $result[] = $data;
            $order++;
        }

        return $result;
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
        $marriages = $this->getAllMarriages($person);
        $children = $person->children()->get();

        $data = [
            'number' => $numbering,
            'person_code' => $person->person_code,
            'full_name_with_nasab' => $person->full_name_with_nasab ?? $person->full_name,
            'gender' => $this->convertGenderToIndonesian($person->gender),
            'birth_date' => $person->birth_date ? $this->formatDateToMonthYear($person->birth_date) : '-',
            'death_date' => $person->death_date ? $this->formatDateToMonthYear($person->death_date) : '-',
            'bio' => $person->bio,
            'photo_path' => $person->photo_path,
            'marriages' => $this->buildMarriagesData($person, $marriages),
            'children_count' => $children->count(),
            'children' => [],
        ];

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
        foreach ($data['generations'] as $generationName => $groups) {
            $output .= "\n" . strtoupper($generationName) . "\n";
            $output .= str_repeat("─", 55) . "\n";

            foreach ($groups as $group) {
                $output .= "\n{$group['parent_info']}\n";
                $output .= str_repeat("•", 30) . "\n";
                
                foreach ($group['members'] as $member) {
                    $output .= $this->formatMemberAsText($member, 2);
                }
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

        // Informasi pernikahan
        if (!empty($member['marriages'])) {
            foreach ($member['marriages'] as $marriage) {
                $spouseName = isset($marriage['spouse']) ? $marriage['spouse']['full_name'] : '-';
                $output .= "{$padding}    Pasangan   : {$spouseName}\n";
                $output .= "{$padding}    Menikah    : {$marriage['marriage_date']}\n";
                if ($marriage['divorce_date'] !== '-') {
                    $output .= "{$padding}    Cerai      : {$marriage['divorce_date']}\n";
                }
            }
        } else {
            $output .= "{$padding}    Status     : Belum Menikah\n";
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