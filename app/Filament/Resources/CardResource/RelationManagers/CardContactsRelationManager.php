<?php

namespace App\Filament\Resources\CardResource\RelationManagers;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CardContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'contacts';

    protected static ?string $title = 'Kontak';

    protected static ?string $modelLabel = 'Kontak';

    protected static ?string $pluralModelLabel = 'Kontak';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Grid::make(2)
                    ->schema([

                        TextInput::make('role')
                            ->label('Peran')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Telepon, WhatsApp, Email'),

                        TextInput::make('phone')
                            ->label('Nomor')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: +6281234567890'),

                    ]),

                Grid::make(2)
                    ->schema([

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),

                        TextInput::make('priority')
                            ->label('Prioritas')
                            ->numeric()
                            ->default(0)
                            ->helperText('Semakin besar angka, semakin prioritas'),

                    ]),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table

            ->reorderable('priority')

            ->defaultSort('priority', 'desc')

            ->columns([

                Tables\Columns\TextColumn::make('role')
                    ->label('Peran')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Nomor')
                    ->copyable()
                    ->copyMessage('Tersalin'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioritas')
                    ->sortable(),

            ])

            ->headerActions([

                Tables\Actions\CreateAction::make(),

            ])

            ->actions([

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make(),

            ])

            ->bulkActions([

                Tables\Actions\BulkActionGroup::make([

                    Tables\Actions\DeleteBulkAction::make(),

                ]),

            ]);
    }
}
