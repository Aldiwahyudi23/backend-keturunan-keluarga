<?php

namespace App\Filament\Resources\PersonResource\Pages;

use App\Filament\Resources\PersonResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPerson extends ViewRecord
{
    protected static string $resource = PersonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('card')
                ->label('Kartu Anggota')
                ->icon('heroicon-o-identification')
                ->color('primary')
                ->url(fn () => route('person.card', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('downloadCard')
                ->label('Download Kartu')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('person.card.download', $this->record))
                ->openUrlInNewTab(),

        ];
    }
}
