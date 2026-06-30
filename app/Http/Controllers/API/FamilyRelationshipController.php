<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Http\Request;
use App\Services\FamilyRelationshipService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class FamilyRelationshipController extends Controller
{
    public function check(
        Request $request,
        FamilyRelationshipService $service
    ) {

        $request->validate([
            'person_a' => ['required'],
            'person_b' => ['required'],
        ]);

        // Cari Person A
        $personA = $this->findPerson($request->person_a);
        
        // Cari Person B
        $personB = $this->findPerson($request->person_b);

        Log::info('Person A found:', ['found' => $personA ? true : false, 'id' => $personA->id ?? null]);
        Log::info('Person B found:', ['found' => $personB ? true : false, 'id' => $personB->id ?? null]);

        if (!$personA || !$personB) {
            $errors = [];
            if (!$personA) $errors['person_a'] = ['Data tidak ditemukan untuk person A'];
            if (!$personB) $errors['person_b'] = ['Data tidak ditemukan untuk person B'];
            
            throw ValidationException::withMessages($errors);
        }

        $result = $service->find($personA, $personB);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    private function findPerson($input): ?Person
    {
        // Coba parse JSON
        $decoded = json_decode($input, true);
        
        if ($decoded !== null && is_array($decoded)) {
            Log::info('Searching by name:', $decoded);
            return $this->findPersonByNameAndParents(
                $decoded['name'] ?? '',
                $decoded['father_name'] ?? null,
                $decoded['mother_name'] ?? null
            );
        }
        
        Log::info('Searching by code:', ['code' => $input]);
        return Person::where('person_code', $input)->first();
    }

    private function findPersonByNameAndParents(
        string $name,
        ?string $fatherName,
        ?string $motherName
    ): ?Person {
        
        if (empty($name)) {
            Log::warning('Name is empty');
            return null;
        }

        if (empty($fatherName) && empty($motherName)) {
            Log::warning('No parent name provided');
            return null;
        }

        // STRATEGI 1: Cari langsung berdasarkan nama (tanpa parent dulu)
        Log::info('Searching person with name:', ['name' => $name]);
        
        // Cari semua orang dengan nama yang mirip
        $persons = Person::where('full_name', 'LIKE', "%{$name}%")->get();
        
        Log::info('Found persons by name:', ['count' => $persons->count()]);
        
        if ($persons->isEmpty()) {
            Log::warning('No person found with name:', ['name' => $name]);
            return null;
        }

        // Jika hanya 1 orang ditemukan, cek parentnya
        if ($persons->count() === 1) {
            $person = $persons->first();
            
            // Cek apakah parent cocok
            if ($this->checkParentMatch($person, $fatherName, $motherName)) {
                Log::info('Person found with parent match:', ['id' => $person->id, 'name' => $person->full_name]);
                return $person;
            }
            
            Log::warning('Parent not match for person:', ['id' => $person->id]);
            return null;
        }

        // Jika banyak orang dengan nama yang sama, cari yang parentnya cocok
        foreach ($persons as $person) {
            if ($this->checkParentMatch($person, $fatherName, $motherName)) {
                Log::info('Person found with parent match (multiple):', ['id' => $person->id, 'name' => $person->full_name]);
                return $person;
            }
        }

        Log::warning('No person with matching parent found');
        return null;
    }

    /**
     * Cek apakah parent cocok dengan yang dicari
     */
    private function checkParentMatch(
        Person $person,
        ?string $fatherName,
        ?string $motherName
    ): bool {
        
        // Ambil semua parent dari person
        $parents = $person->parents()->get();
        
        Log::info('Checking parents for person:', [
            'person_id' => $person->id,
            'person_name' => $person->full_name,
            'parents_count' => $parents->count()
        ]);

        $hasFatherMatch = false;
        $hasMotherMatch = false;

        foreach ($parents as $parent) {
            // Cek apakah parent adalah ayah (gender Laki-laki) atau ibu (gender Perempuan)
            // Jika tidak ada gender, coba cek berdasarkan logika
            $isFather = $parent->gender === 'Laki-laki' || $parent->gender === 'male';
            $isMother = $parent->gender === 'Perempuan' || $parent->gender === 'female';
            
            Log::info('Parent:', [
                'id' => $parent->id,
                'name' => $parent->full_name,
                'gender' => $parent->gender ?? 'unknown',
                'isFather' => $isFather,
                'isMother' => $isMother
            ]);

            // Cek kecocokan nama (case insensitive, flexible)
            if ($fatherName && $isFather) {
                if ($this->isNameMatch($parent->full_name, $fatherName)) {
                    $hasFatherMatch = true;
                    Log::info('Father match found:', ['db' => $parent->full_name, 'search' => $fatherName]);
                }
            }

            if ($motherName && $isMother) {
                if ($this->isNameMatch($parent->full_name, $motherName)) {
                    $hasMotherMatch = true;
                    Log::info('Mother match found:', ['db' => $parent->full_name, 'search' => $motherName]);
                }
            }

            // Jika tidak ada gender, coba match dengan kedua parent
            if (empty($parent->gender)) {
                if ($fatherName && $this->isNameMatch($parent->full_name, $fatherName)) {
                    $hasFatherMatch = true;
                    Log::info('Father match found (no gender):', ['db' => $parent->full_name, 'search' => $fatherName]);
                }
                if ($motherName && $this->isNameMatch($parent->full_name, $motherName)) {
                    $hasMotherMatch = true;
                    Log::info('Mother match found (no gender):', ['db' => $parent->full_name, 'search' => $motherName]);
                }
            }
        }

        // Return true jika:
        // - Ada father match (jika fatherName diberikan)
        // - Ada mother match (jika motherName diberikan)
        // - Atau keduanya match (jika keduanya diberikan)
        if ($fatherName && $motherName) {
            return $hasFatherMatch && $hasMotherMatch;
        } elseif ($fatherName) {
            return $hasFatherMatch;
        } elseif ($motherName) {
            return $hasMotherMatch;
        }

        return false;
    }

    /**
     * Cek kecocokan nama dengan lebih fleksibel
     */
    private function isNameMatch(string $dbName, string $searchName): bool
    {
        // Bersihkan nama (hilangkan spasi berlebih, lower case)
        $dbName = trim(strtolower($dbName));
        $searchName = trim(strtolower($searchName));

        // Cek apakah searchName ada di dbName (contains)
        if (strpos($dbName, $searchName) !== false) {
            return true;
        }

        // Cek apakah dbName ada di searchName (contains)
        if (strpos($searchName, $dbName) !== false) {
            return true;
        }

        // Cek similarity (jika mirip > 80%)
        similar_text($dbName, $searchName, $percent);
        if ($percent > 80) {
            return true;
        }

        return false;
    }
}