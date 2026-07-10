<?php

namespace App\Filament\Resources\CardResource\Pages;

use App\Filament\Resources\CardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCard extends EditRecord
{
    protected static string $resource = CardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('previewPdf')
                ->label('Preview PDF')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->url(fn () => route('card.preview', $this->record))
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->status === 'draft'),

            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
