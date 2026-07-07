<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBook extends EditRecord
{
    protected static string $resource = BookResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Actions\Action::make('previewPdf')
                ->label('Preview PDF')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->url(fn () => route('books.preview', $this->record))
                ->openUrlInNewTab(),

            Actions\ViewAction::make(),

            Actions\DeleteAction::make(),

        ];
    }
}