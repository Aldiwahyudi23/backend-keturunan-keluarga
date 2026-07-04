<?php

namespace App\Services\Book;

use App\Models\Book;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class BookPdfService
{
    public function __construct(
        protected BookDataService $bookDataService
    ) {
    }

    /**
     * Preview PDF.
     */
    public function generate(Book $book): Response
    {
        if ($book->status !== 'draft') {
            abort(403, 'Preview hanya tersedia untuk buku yang masih berstatus draft.');
        }

        $result = $this->bookDataService->generate($book);

        if (! $result['success']) {
            abort(404, $result['message']);
        }

        $data = $result['data'];

        $view = $book->template?->blade_view ?: 'book.classic';

        $pdf = Pdf::loadView($view, $data)
            ->setPaper('a4', 'portrait');

        return $pdf->stream(
            str($book->title)->slug() . '.pdf'
        );
    }

    /**
     * Download PDF.
     */
    public function download(Book $book): Response
    {
        if ($book->status !== 'published') {
            abort(403, 'Buku masih dalam tahap pembuatan dan belum dapat diunduh.');
        }

        $result = $this->bookDataService->generate($book);

        if (! $result['success']) {
            abort(404, $result['message']);
        }

        $data = $result['data'];

        $view = $book->template?->blade_view ?: 'book.classic';

        $pdf = Pdf::loadView($view, $data)
            ->setPaper('a4', 'portrait');

        return $pdf->download(
            str($book->title)->slug() . '.pdf'
        );
    }
}