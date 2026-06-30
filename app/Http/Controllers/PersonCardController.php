<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Services\PersonCardService;
use Illuminate\Http\Request;

class PersonCardController extends Controller
{
    public function download(Person $person)
    {
        $service = new PersonCardService();
        return $service->generateCard($person, true);
    }
}