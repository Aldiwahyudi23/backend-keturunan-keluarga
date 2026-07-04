<?php

namespace App\Filament\Resources\BookTemplateResource\Pages;

use App\Filament\Resources\BookTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBookTemplate extends CreateRecord
{
    protected static string $resource = BookTemplateResource::class;
}
