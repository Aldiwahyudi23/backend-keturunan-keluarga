<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Report\BookDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function __construct(
        protected BookDataService $bookDataService
    ) {
    }

    /**
     * Generate data buku (JSON)
     *
     * GET /api/book/{uuid}
     *
     * Query:
     * level=2
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $maxGenerations = (int) $request->get('level', 0);

        $result = $this->bookDataService->generate(
            $uuid,
            $maxGenerations
        );

        if (!$result['success']) {
            return response()->json($result, 404);
        }

        return response()->json($result);
    }
}