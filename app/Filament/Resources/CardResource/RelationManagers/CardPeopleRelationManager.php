<?php

namespace App\Filament\Resources\CardResource\RelationManagers;

use App\Models\Person;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CardPeopleRelationManager extends RelationManager
{
    protected static string $relationship = 'cardPeople';

    protected static ?string $title = 'Anggota Kartu';

    protected static ?string $modelLabel = 'Anggota';

    protected static ?string $pluralModelLabel = 'Anggota Kartu';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Grid::make(2)
                    ->schema([

                        Select::make('person_id')
                            ->label('Orang')
                            ->columnSpanFull()
                            ->relationship(
                                name: 'person',
                                titleAttribute: 'full_name'
                            )
                            ->getOptionLabelFromRecordUsing(fn (Person $record) => $record->full_name_with_nasab)
                            ->searchable(['full_name'])
                            ->preload()
                            ->required(),

                        FileUpload::make('photo_path')
                            ->label('Foto')
                            ->image()
                            ->directory('card/people-photos')
                            ->helperText('Disarankan menggunakan foto formal ukuran 2×3 atau 3×4 agar hasil cetak kartu lebih rapi.'),

                        Textarea::make('address')
                            ->label('Alamat')
                            ->rows(3),

                    ]),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table

            ->columns([

                Tables\Columns\TextColumn::make('person.full_name')
                    ->label('Nama')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->width(50)
                    ->height(50)
                    ->circular(),

                Tables\Columns\TextColumn::make('address')
                    ->label('Alamat')
                    ->limit(30),

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
