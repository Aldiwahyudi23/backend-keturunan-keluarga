<?php

namespace App\Filament\Resources\BookResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BookSectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';

    protected static ?string $title = 'Isi Buku';

    protected static ?string $modelLabel = 'Section';

    protected static ?string $pluralModelLabel = 'Daftar Isi Buku';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Grid::make(2)
                    ->schema([

                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('sort')
                            ->label('Urutan')
                            ->numeric()
                            ->default(1)
                            ->required(),

                    ]),

                Select::make('type')
                    ->label('Jenis Konten')
                    ->required()
                    ->native(false)
                    ->live()
                    ->options([
                        'text' => 'Teks Manual',
                        'dynamic' => 'Data Otomatis',
                    ]),

                Select::make('key')
                    ->label('Data Otomatis')
                    ->native(false)
                    ->visible(fn ($get) => $get('type') === 'dynamic')
                    ->required(fn ($get) => $get('type') === 'dynamic')
                    ->options([

                        'cover' => 'Cover',

                        'toc' => 'Daftar Isi',

                        'root' => 'Profil Tokoh',

                        'genealogy' => 'Silsilah',

                        'history' => 'Riwayat Hidup',

                        'image' => 'Image',

                    ]),

                RichEditor::make('content')
                    ->label('Isi')
                    ->visible(fn ($get) => $get('type') === 'text')
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'bulletList',
                        'orderedList',
                        'blockquote',
                        'h2',
                        'h3',
                        'alignLeft',
                        'alignCenter',
                        'alignRight',
                    ])
                    ->disableGrammarly(),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table

            ->reorderable('sort')

            ->defaultSort('sort')

            ->columns([

                Tables\Columns\TextColumn::make('sort')
                    ->label('#')
                    ->sortable()
                    ->width(60),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Jenis')
                    ->colors([
                        'success' => 'text',
                        'warning' => 'dynamic',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'text' => 'Manual',
                        'dynamic' => 'Otomatis',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('key')
                    ->label('Key'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

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