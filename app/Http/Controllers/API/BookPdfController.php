<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Services\Book\BookPdfService;
use Symfony\Component\HttpFoundation\Response;

class BookPdfController extends Controller
{
    public function __construct(
        protected BookPdfService $bookPdfService
    ) {}

    /**
     * Preview PDF.
     */
    public function preview(int $bookId): Response
    {
        $book = Book::with([
            'rootPerson.fatherRelation.parent',
            'template',
            'sections',
        ])->findOrFail($bookId);

        return $this->bookPdfService->generate($book);
    }

    /**
     * Download PDF.
     */
    public function download(int $bookId): Response
    {
        $book = Book::with([
            'rootPerson.fatherRelation.parent',
            'template',
            'sections',
        ])->findOrFail($bookId);

        return $this->bookPdfService->download($book);
    }
}
