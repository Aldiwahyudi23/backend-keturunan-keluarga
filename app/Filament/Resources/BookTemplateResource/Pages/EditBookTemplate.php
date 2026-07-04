<?php

namespace App\Filament\Resources\BookTemplateResource\Pages;

use App\Filament\Resources\BookTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBookTemplate extends EditRecord
{
    protected static string $resource = BookTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
