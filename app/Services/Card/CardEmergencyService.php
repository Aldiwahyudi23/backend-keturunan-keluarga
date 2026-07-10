<?php

namespace App\Services\Card;

use App\Models\Card\Card;
use App\Models\Person;

class CardEmergencyService
{
    public function getEmergencyData(string $personUuid, string $cardUuid): ?array
    {
        $card = Card::where('uuid', $cardUuid)->first();

        if (! $card) {
            return null;
        }

        $person = Person::where('uuid', $personUuid)->first();

        if (! $person) {
            return null;
        }

        $cardPerson = $card->cardPeople()
            ->where('person_id', $person->id)
            ->first();

        if (! $cardPerson) {
            return null;
        }

        $contacts = $card->contacts()
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();

        $formattedContacts = [];

        foreach ($contacts as $contact) {
            $phone = $this->formatPhone($contact->phone);
            $message = 'Assalamualaikum'."\n\n".'apakah ini dengan '.$contact->role.'?';
            $waUrl = 'https://wa.me/'.$phone.'?text='.rawurlencode($message);

            $formattedContacts[] = [
                'role' => $contact->role,
                'phone' => $phone,
                'url' => $waUrl,
            ];
        }

        return [
            'contacts' => $formattedContacts,
            'address' => $cardPerson->address,
        ];
    }

    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            return '62'.substr($phone, 1);
        }

        if (str_starts_with($phone, '62')) {
            return $phone;
        }

        return '62'.$phone;
    }
}
