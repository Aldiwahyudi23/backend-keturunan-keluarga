<?php

namespace App\Filament\Resources\PersonResource\Pages;

use App\Filament\Resources\PersonResource;
use App\Services\PersonCardService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPersonCard extends ViewRecord
{
    protected static string $resource = PersonResource::class;

    protected static string $view = 'filament.resources.person-resource.pages.view-person-card';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download')
                ->label('Download PDF')
                ->icon('heroicon-o-download')
                ->color('success')
                ->url(fn () => route('filament.admin.resources.people.download-card', $this->record)),
            
            Actions\Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->url(fn () => route('filament.admin.resources.people.view', $this->record)),
        ];
    }

    public function getCardHtml()
    {
        $service = new PersonCardService();
        $pdf = $service->generateCard($this->record, false);
        return $pdf->output();
    }
}