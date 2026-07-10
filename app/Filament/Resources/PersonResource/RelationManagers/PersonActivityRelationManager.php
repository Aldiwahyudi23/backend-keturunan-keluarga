<?php

namespace App\Filament\Resources\PersonResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PersonActivityRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $title = 'Aktivitas';

    protected static ?string $modelLabel = 'Aktivitas';

    protected static ?string $pluralModelLabel = 'Aktivitas';

    public function table(Table $table): Table
    {
        return $table

            ->defaultSort('created_at', 'desc')

            ->columns([

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->width(120),

                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\IconColumn::make('can_parent_view')
                    ->label('Parent')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->badge()
                    ->color('info'),

            ])

            ->filters([

                Tables\Filters\TernaryFilter::make('can_parent_view')
                    ->label('Bisa Dilihat Orang Tua'),

            ])

            ->headerActions([])

            ->actions([])

            ->bulkActions([]);
    }
}
