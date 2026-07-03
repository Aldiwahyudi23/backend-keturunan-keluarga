<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Report\BookPdfService;
use Illuminate\Http\Request;

class BookPdfController extends Controller
{
    public function __construct(
        protected BookPdfService $bookPdfService
    ) {
    }

    public function show(Request $request, string $uuid)
    {
        return $this->bookPdfService->generate(
            $uuid,
            (int) $request->get('level', 0)
        );
    }

    public function download(Request $request, string $uuid)
    {
        return $this->bookPdfService->download(
            $uuid,
            (int) $request->get('level', 0)
        );
    }
}