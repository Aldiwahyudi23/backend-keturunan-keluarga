<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Services\PersonCardService;
use Symfony\Component\HttpFoundation\Response;

class PersonCardController extends Controller
{
    public function __construct(
        protected PersonCardService $service
    ) {
    }

    /**
     * Preview.
     */
    public function show(Person $person): Response
    {
        return $this->service->generate($person);
    }

    /**
     * Download.
     */
    public function download(Person $person): Response
    {
        return $this->service->download($person);
    }
}