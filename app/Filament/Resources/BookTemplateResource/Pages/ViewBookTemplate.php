<?php

namespace App\Filament\Resources\BookTemplateResource\Pages;

use App\Filament\Resources\BookTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBookTemplate extends ViewRecord
{
    protected static string $resource = BookTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
