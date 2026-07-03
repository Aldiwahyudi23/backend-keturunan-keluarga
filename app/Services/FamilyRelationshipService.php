<?php

namespace App\Services;

use App\Models\Person;
use App\Models\Marriage;
use Illuminate\Support\Collection;

class FamilyRelationshipService
{
    public function find(
        Person $personA,
        Person $personB
    ): array {

        $ancestorsA = $this->getAncestors($personA);
        $ancestorsB = $this->getAncestors($personB);
        $common = $this->findNearestCommonAncestor($ancestorsA, $ancestorsB);

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
                'uuid' => $common['person']->uuid,
                'name' => $common['person']->full_name,
            ],
            'relationship' => $relationship,
            'path_a' => $common['path_a'],
            'path_b' => $common['path_b'],
            'story' => $this->buildStory($common),
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
            $this->getAncestors($parent, $level + 1, $result, $path);
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
            $distance = $ancestorA['level'] + $ancestorB['level'];

            if (!$nearest || $distance < $nearest['distance']) {
                $nearest = [
                    'person' => $ancestorA['person'],
                    'level_a' => $ancestorA['level'],
                    'level_b' => $ancestorB['level'],
                    'path_a' => $ancestorA['path'],
                    'path_b' => $ancestorB['path'],
                    'distance' => $distance,
                ];
            }
        }

        return $nearest;
    }

    private function detectRelationship(
        int $levelA,
        int $levelB
    ): string {

        if ($levelA === 1 && $levelB === 1) {
            return 'Saudara Kandung';
        }

        if ($levelA === 2 && $levelB === 2) {
            return 'Sepupu';
        }

        if ($levelA === 3 && $levelB === 3) {
            return 'Sepupu Dua Kali';
        }

        if ($levelA === 1 && $levelB > 1) {
            return 'Paman/Bibi';
        }

        if ($levelB === 1 && $levelA > 1) {
            return 'Keponakan';
        }

        return 'Keluarga';
    }

    /**
     * Build main story with HTML format
     */
    private function buildStory(array $common): string
    {
        $ancestor = $common['person']->full_name;
        $relationship = $this->detectRelationship($common['level_a'], $common['level_b']);
        
        // Get names from paths (first element is the person itself)
        $personAName = $common['path_a'][0] ?? 'Orang pertama';
        $personBName = $common['path_b'][0] ?? 'Orang kedua';
        
        // Get spouse of ancestor
        $spouseName = $this->getSpouseInfo($ancestor);
        
        // Get relevant children of ancestor (only those in the paths)
        $relevantChildren = $this->getRelevantChildren($common['path_a'], $common['path_b']);
        
        // Build intro paragraph about ancestor
        $introText = $ancestor;
        if ($spouseName) {
            $introText .= " menikah dengan {$spouseName}";
        }
        if (!empty($relevantChildren)) {
            $introText .= " dan dikaruniai beberapa anak";
            if (count($relevantChildren) > 1) {
                $lastChild = array_pop($relevantChildren);
                $introText .= " salah satunya adalah <strong>" . implode("</strong>, <strong>", $relevantChildren) . "</strong> dan <strong>{$lastChild}</strong>";
            } else {
                $introText .= " salah satunya adalah <strong>{$relevantChildren[0]}</strong>";
            }
        }
        $introText .= ". Nah ini bakal cikal hubungan kalian.";
        
        // Build detailed narrative for both paths
        $pathANarrative = $this->buildPathNarrative($common['path_a'], $personAName, $ancestor);
        $pathBNarrative = $this->buildPathNarrative($common['path_b'], $personBName, $ancestor);
        
        $html = <<<HTML
        <p><strong>Hubungan Keluarga</strong></p>
        
        <p>Setelah melakukan penelusuran silsilah keluarga, ditemukan bahwa <strong>{$personAName}</strong> dan <strong>{$personBName}</strong> memiliki hubungan kekerabatan melalui leluhur yang sama yaitu <strong>{$ancestor}</strong>.</p>
        
        <p>{$introText}</p>
        
        <p><strong>Jalur Pertama:</strong> {$pathANarrative}</p>
        
        <p><strong>Jalur Kedua:</strong> {$pathBNarrative}</p>
        
        <p><strong>Kesimpulan:</strong> Berdasarkan struktur keluarga yang ditemukan, <strong>{$personAName}</strong> dan <strong>{$personBName}</strong> memiliki hubungan sebagai <strong><em>{$relationship}</em></strong>.</p>
        
        <p><em>💝 Ternyata kalian adalah satu keluarga! Mari selalu jaga kebersamaan, saling peduli dan perhatikan satu sama lain. Jangan biarkan jarak atau kesibukan memisahkan tali silaturahmi ini. Ingatlah, keluarga adalah tempat ternyaman untuk pulang. Rukun, harmonis, dan saling mendukung adalah kunci kebahagiaan keluarga.</em></p>
        
        <p style="text-align: right;"><em><strong>~ Bersama kita kuat, saling menjaga kita bahagia ~</strong></em></p>
        HTML;
        
        return $html;
    }

    /**
     * Get only relevant children of ancestor that appear in the paths
     */
    private function getRelevantChildren(array $pathA, array $pathB): array
    {
        $children = [];
        
        // Reverse paths to start from ancestor
        $reversedA = array_reverse($pathA);
        $reversedB = array_reverse($pathB);
        
        // Get the first child after ancestor from both paths
        if (count($reversedA) > 1) {
            $childA = $reversedA[1];
            if (!in_array($childA, $children)) {
                $children[] = $childA;
            }
        }
        
        if (count($reversedB) > 1) {
            $childB = $reversedB[1];
            if (!in_array($childB, $children)) {
                $children[] = $childB;
            }
        }
        
        return $children;
    }

    /**
     * Build detailed narrative for a family path
     */
    private function buildPathNarrative(array $path, string $targetPerson, string $ancestor): string
    {
        // Reverse path to start from ancestor
        $reversedPath = array_reverse($path);
        $count = count($reversedPath);
        
        if ($count <= 1) {
            return "{$targetPerson} adalah leluhur itu sendiri.";
        }
        
        // Get the first child after ancestor
        $firstChild = $reversedPath[1] ?? '';
        
        // Check if target is directly the child of ancestor (only 2 generations)
        if ($count === 2) {
            // Target is the child of ancestor
            $result = "<strong>{$targetPerson}</strong> adalah anak dari <strong>{$ancestor}</strong>";
            return $result . ".";
        }
        
        // For 3+ generations, build narrative with spouse info
        $storyParts = [];
        
        // Build narrative with correct spouse for each generation
        for ($i = 1; $i < $count; $i++) {
            $currentPerson = $reversedPath[$i];
            $nextPerson = $i + 1 < $count ? $reversedPath[$i + 1] : null;
            
            if ($i === 1) {
                // First child of ancestor
                $storyParts[] = "<strong>{$currentPerson}</strong>";
                
                if ($nextPerson) {
                    // Get the correct spouse based on the child
                    $correctSpouse = $this->getSpouseForChild($currentPerson, $nextPerson);
                    if ($correctSpouse) {
                        $storyParts[] = "menikah dengan {$correctSpouse}";
                    }
                    $storyParts[] = "dan memiliki anak yaitu <strong>{$nextPerson}</strong>";
                }
            } else if ($i === $count - 1) {
                // Last person is the target - already handled
                break;
            } else {
                // Middle generations
                $nextPerson2 = $i + 1 < $count ? $reversedPath[$i + 1] : null;
                
                if ($nextPerson2) {
                    $correctSpouse = $this->getSpouseForChild($currentPerson, $nextPerson2);
                    if ($correctSpouse) {
                        $storyParts[] = "yang kemudian menikah dengan {$correctSpouse}";
                    }
                    $storyParts[] = "dan memiliki anak yaitu <strong>{$nextPerson2}</strong>";
                }
            }
        }
        
        $result = implode(' ', $storyParts);
        
        // Add conclusion with correct generation label
        $generationLabel = $this->getSimplifiedGenerationLabel($count - 1);
        $result .= ". Dengan demikian, <strong>{$targetPerson}</strong> adalah {$generationLabel} dari <strong>{$ancestor}</strong>.";
        
        return $result;
    }

    /**
     * Get the correct spouse for a parent based on their child
     */
    private function getSpouseForChild(string $parentName, string $childName): ?string
    {
        $parent = Person::where('full_name', $parentName)->first();
        
        if (!$parent) {
            return null;
        }
        
        $child = Person::where('full_name', $childName)->first();
        
        if (!$child) {
            return null;
        }
        
        // Get all marriages of the parent
        $marriages = $this->getMarriagesForPerson($parent);
        
        if ($marriages->isEmpty()) {
            return null;
        }
        
        // Find which spouse is the parent of the child
        foreach ($marriages as $marriage) {
            // Check if this marriage produced the child
            $spouse = $this->getSpouseFromMarriage($parent, $marriage);
            if ($spouse && $this->isParentOfChild($spouse, $child)) {
                return $spouse->full_name;
            }
        }
        
        // Fallback: if no marriage found, try to find any spouse that is parent of child
        $spouses = $this->getAllSpouses($parent);
        foreach ($spouses as $spouse) {
            if ($this->isParentOfChild($spouse, $child)) {
                return $spouse->full_name;
            }
        }
        
        // If still not found, return first spouse
        $firstSpouse = $marriages->first();
        if ($firstSpouse) {
            $spouse = $this->getSpouseFromMarriage($parent, $firstSpouse);
            return $spouse ? $spouse->full_name : null;
        }
        
        return null;
    }

    /**
     * Get marriages for a person
     */
    private function getMarriagesForPerson(Person $person)
    {
        if ($person->gender === 'male') {
            return $person->marriagesAsHusband;
        } elseif ($person->gender === 'female') {
            return $person->marriagesAsWife;
        }
        
        return collect();
    }

    /**
     * Get spouse from marriage
     */
    private function getSpouseFromMarriage(Person $person, Marriage $marriage): ?Person
    {
        if ($person->gender === 'male') {
            return $marriage->wife;
        } elseif ($person->gender === 'female') {
            return $marriage->husband;
        }
        
        return null;
    }

    /**
     * Check if a person is a parent of a child
     */
    private function isParentOfChild(Person $parent, Person $child): bool
    {
        // Check if the child has this person as parent
        $parents = $child->parents()->get();
        
        foreach ($parents as $childParent) {
            if ($childParent->id === $parent->id) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get all spouses of a person
     */
    private function getAllSpouses(Person $person): Collection
    {
        if ($person->gender === 'male') {
            return $person->marriagesAsHusband()
                ->with('wife')
                ->get()
                ->pluck('wife')
                ->filter();
        }
        
        if ($person->gender === 'female') {
            return $person->marriagesAsWife()
                ->with('husband')
                ->get()
                ->pluck('husband')
                ->filter();
        }
        
        return collect();
    }

    /**
     * Get spouse information for a person (legacy method - kept for compatibility)
     * Deprecated: Use getSpouseForChild instead when context is available
     */
    private function getSpouseInfo(string $personName): ?string
    {
        $person = Person::where('full_name', $personName)->first();
        
        if (!$person) {
            return null;
        }
        
        // Get all spouses
        $spouses = $this->getAllSpouses($person);
        
        if ($spouses->isEmpty()) {
            return null;
        }
        
        // Get the first spouse
        $spouse = $spouses->first();
        if (!$spouse) {
            return null;
        }
        
        return $spouse->full_name;
    }

    /**
     * Get simplified generation label (without ordinal number)
     */
    private function getSimplifiedGenerationLabel(int $level): string
    {
        $labels = [
            0 => 'diri sendiri',
            1 => 'anak',
            2 => 'cucu',
            3 => 'cicit',
            4 => 'buyut',
            5 => 'piut',
            6 => 'anggkat',
        ];
        
        return $labels[$level] ?? "generasi ke-{$level}";
    }
}