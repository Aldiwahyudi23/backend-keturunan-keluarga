<?php

namespace App\Http\Controllers;

use App\Models\Card\Card;
use App\Services\Card\CardPersonService;
use Symfony\Component\HttpFoundation\Response;

class CardController extends Controller
{
    public function __construct(
        protected CardPersonService $service
    ) {}

    public function preview(Card $card): Response
    {
        return $this->service->generate($card);
    }

    public function download(Card $card): Response
    {
        return $this->service->download($card);
    }
}
