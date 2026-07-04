<?php

namespace App\Filament\Resources\BookTemplateResource\Pages;

use App\Filament\Resources\BookTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookTemplates extends ListRecords
{
    protected static string $resource = BookTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
