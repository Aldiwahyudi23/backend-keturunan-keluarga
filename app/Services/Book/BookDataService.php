<?php

namespace App\Services\Book;

use App\Models\Book;
use App\Models\BookSection;
use App\Services\Report\GenealogyService;
use App\Services\Report\HistoryService;
use Carbon\Carbon;

class BookDataService
{
    public function __construct(
        protected GenealogyService $genealogyService,
        protected HistoryService $historyService,
    ) {}

    /**
     * Menyusun seluruh data buku.
     */
    public function generate(Book $book): array
    {
        $person = $book->rootPerson;

        if (! $person) {
            return [
                'success' => false,
                'message' => 'Root person tidak ditemukan.',
                'data' => null,
            ];
        }

        $genealogy = $this->genealogyService
            ->generateGeneology(
                $person->uuid,
                $book->default_max_generation
            );

        $history = $this->historyService
            ->generate($person->id);

        return [

            'success' => true,

            'message' => 'Book berhasil disusun.',

            'data' => [

                /*
                |--------------------------------------------------------------------------
                | Metadata
                |--------------------------------------------------------------------------
                */

                'book' => [

                    'id' => $book->id,

                    'uuid' => $book->uuid,

                    'title' => $book->title,

                    'edition' => $book->edition,

                    'version' => $book->version,

                    'status' => $book->status,

                    'show_cover' => $book->show_cover,

                    'show_table_of_contents' => $book->show_table_of_contents,

                    'published_at' => $book->published_at,

                    'template' => $book->template?->blade_view,

                    'generated_at' => Carbon::now(),

                ],

                /*
                |--------------------------------------------------------------------------
                | Cover
                |--------------------------------------------------------------------------
                */

                'cover' => [

                    'title' => $book->cover_title,

                    'subtitle' => $book->cover_subtitle,

                    'logo' => $book->cover_logo,

                    'background' => $book->cover_background,

                    'quote' => $book->cover_quote,

                    'footer' => $book->cover_footer,

                    'full_name' => $person->full_name,

                    'full_name_with_nasab' => $person->full_name_with_nasab,

                    'nasab' => $person->nasab,

                    'father_name' => $person->father?->full_name,

                    'year' => Carbon::now()->year,

                ],

                /*
                |--------------------------------------------------------------------------
                | Sections
                |--------------------------------------------------------------------------
                */

                'sections' => $this->buildSections(
                    $book,
                    $history['data'],
                    $genealogy['data']
                ),

            ],

        ];
    }

    /**
     * Menyusun seluruh section sesuai urutan.
     */
    protected function buildSections(
        Book $book,
        array $history,
        array $genealogy
    ): array {

        return $book->sections
            ->sortBy('sort')
            ->map(function (BookSection $section) use ($history, $genealogy) {

                $data = match ($section->key) {

                    'history' => $history,

                    'genealogy' => $genealogy,

                    default => [
                        'title' => $section->title,
                        'content' => $section->content,
                    ],

                };

                return [

                    'id' => $section->id,

                    'title' => $section->title,

                    'type' => $section->type,

                    'key' => $section->key,

                    'sort' => $section->sort,

                    'data' => $data,

                ];
            })
            ->values()
            ->toArray();
    }
}
