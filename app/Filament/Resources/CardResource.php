<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardResource\Pages;
use App\Filament\Resources\CardResource\RelationManagers\CardContactsRelationManager;
use App\Filament\Resources\CardResource\RelationManagers\CardPeopleRelationManager;
use App\Models\Card\Card;
use App\Models\Person;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CardResource extends Resource
{
    protected static ?string $model = Card::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'Kartu Nama';

    protected static ?string $pluralLabel = 'Kartu Nama';

    protected static ?string $modelLabel = 'Kartu Nama';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Informasi Kartu')
                    ->icon('heroicon-o-identification')
                    ->schema([

                        Grid::make(2)
                            ->schema([

                                TextInput::make('name')
                                    ->label('Nama Kartu')
                                    ->required()
                                    ->maxLength(255),

                                Select::make('card_template_id')
                                    ->label('Template')
                                    ->relationship('template', 'name')
                                    ->searchable()
                                    ->preload(),

                                TextInput::make('title')
                                    ->label('Title')
                                    ->maxLength(255),

                                TextInput::make('subtitle')
                                    ->label('Subtitle')
                                    ->maxLength(255),

                                Select::make('root_person_id')
                                    ->label('Tokoh Utama')
                                    ->relationship(
                                        name: 'rootPerson',
                                        titleAttribute: 'full_name'
                                    )
                                    ->getOptionLabelFromRecordUsing(fn (Person $record) => $record->full_name_with_nasab)
                                    ->searchable(['full_name'])
                                    ->preload()
                                    ->required(),

                                Select::make('status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'published' => 'Published',
                                    ])
                                    ->default('draft')
                                    ->required(),

                            ]),

                    ]),

                Section::make('Tampilan')
                    ->icon('heroicon-o-photo')
                    ->schema([

                        Grid::make(2)
                            ->schema([

                                FileUpload::make('logo_path')
                                    ->label('Logo')
                                    ->image()
                                    ->directory('card/logo')
                                    ->helperText('Disarankan menggunakan logo formal ukuran 1×1 agar hasil cetak kartu lebih rapi.'),

                                FileUpload::make('background_path')
                                    ->label('Background')
                                    ->image()
                                    ->directory('card/background')
                                    ->helperText('Disarankan menggunakan background formal ukuran 90x55 mm (ukuran KTA standar) agar hasil cetak kartu lebih rapi.'),

                            ]),

                    ]),

                Section::make('Catatan')
                    ->icon('heroicon-o-document-text')
                    ->schema([

                        RichEditor::make('note')
                            ->label('Note')
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
                            ]),

                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable(),

                Tables\Columns\TextColumn::make('template.name')
                    ->label('Template')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('rootPerson.full_name')
                    ->label('Tokoh')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                    ]),

                Tables\Columns\TextColumn::make('contacts_count')
                    ->counts('contacts')
                    ->label('Kontak')
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ]),

                Tables\Filters\SelectFilter::make('card_template_id')
                    ->label('Template')
                    ->relationship('template', 'name'),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CardContactsRelationManager::class,
            CardPeopleRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCards::route('/'),
            'create' => Pages\CreateCard::route('/create'),
            'view' => Pages\ViewCard::route('/{record}'),
            'edit' => Pages\EditCard::route('/{record}/edit'),
        ];
    }
}
