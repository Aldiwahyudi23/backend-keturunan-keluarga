<?php

namespace App\Services\Report;

use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class BookPdfService
{
    public function __construct(
        protected BookDataService $bookDataService
    ) {
    }

    /**
     * Generate PDF Buku Silsilah.
     */
    public function generate(
        string $uuid,
        int $maxGenerations = 0
    ): Response {

        $result = $this->bookDataService
            ->generate($uuid, $maxGenerations);

        if (!$result['success']) {
            abort(404, $result['message']);
        }

        $data = $result['data'];

        $pdf = Pdf::loadView(
            'pdf.book',
            $data
        );

        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream(
            'buku-silsilah-' .
            str($data['cover']['full_name'])->slug() .
            '.pdf'
        );
    }

    /**
     * Download PDF.
     */
    public function download(
        string $uuid,
        int $maxGenerations = 0
    ): Response {

        $result = $this->bookDataService
            ->generate($uuid, $maxGenerations);

        if (!$result['success']) {
            abort(404, $result['message']);
        }

        $data = $result['data'];

        $pdf = Pdf::loadView(
            'pdf.book',
            $data
        );

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download(
            'Buku Silsilah - ' .
            $data['cover']['full_name'] .
            '.pdf'
        );
    }
}