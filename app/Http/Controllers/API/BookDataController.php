<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Services\Book\BookDataService;
use Illuminate\Http\JsonResponse;

class BookDataController extends Controller
{
    public function __construct(
        protected BookDataService $bookDataService
    ) {}

    /**
     * Menampilkan hasil data buku (JSON).
     */
    public function show(int $bookId): JsonResponse
    {
        $book = Book::with([
            // 'rootPerson.fatherRelation.parent',
            // 'template',
            // 'sections',
        ])->find($bookId);

        if (! $book) {
            return response()->json([
                'success' => false,
                'message' => 'Book tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        return response()->json(
            $this->bookDataService->generate($book)
        );
    }
}
