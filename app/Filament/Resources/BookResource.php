<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Filament\Resources\BookResource\RelationManagers\BookSectionsRelationManager;
use App\Models\Book;
use App\Models\Person;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Buku';

    protected static ?string $pluralLabel = 'Buku';

    protected static ?string $modelLabel = 'Buku';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Informasi Buku')
                    ->icon('heroicon-o-book-open')
                    ->schema([

                        Grid::make(2)
                            ->schema([

                                TextInput::make('title')
                                    ->label('Judul Buku')
                                    ->required()
                                    ->maxLength(255),

                                Select::make('template_id')
                                    ->label('Template')
                                    ->relationship('template', 'name')
                                    ->searchable()
                                    ->preload(),

                                TextInput::make('edition')
                                    ->label('Edisi')
                                    ->placeholder('Contoh : Edisi I'),

                                TextInput::make('version')
                                    ->label('Versi')
                                    ->placeholder('Contoh : v1.0'),

                                Select::make('root_person_id')
                                    ->label('Tokoh Utama')
                                    ->relationship('rootPerson', 'full_name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                TextInput::make('default_max_generation')
                                    ->label('Generasi Default')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('0 = seluruh generasi'),

                            ]),

                    ]),

                Section::make('Cover Buku')
                    ->icon('heroicon-o-photo')
                    ->schema([

                        Grid::make(2)
                            ->schema([

                                FileUpload::make('cover_logo')
                                    ->label('Logo')
                                    ->image()
                                    ->directory('book/logo'),

                                FileUpload::make('cover_background')
                                    ->label('Background')
                                    ->image()
                                    ->directory('book/background'),

                            ]),

                        TextInput::make('cover_title')
                            ->label('Judul Cover')
                            ->required(),

                        TextInput::make('cover_subtitle')
                            ->label('Sub Judul'),

                        Textarea::make('cover_quote')
                            ->label('Quote Cover')
                            ->rows(4)
                            ->columnSpanFull(),

                        Textarea::make('cover_footer')
                            ->label('Footer Cover')
                            ->rows(3)
                            ->helperText('Misal: Yayasan Keluarga Mahaya • www.domain.com')
                            ->columnSpanFull(),

                    ]),

                Section::make('Pengaturan')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([

                        Grid::make(3)
                            ->schema([

                                Toggle::make('show_cover')
                                    ->label('Tampilkan Cover')
                                    ->default(true),

                                Toggle::make('show_table_of_contents')
                                    ->label('Daftar Isi')
                                    ->default(true),

                                Select::make('status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'published' => 'Published',
                                    ])
                                    ->default('draft')
                                    ->required(),

                            ]),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Tanggal Publish'),

                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('edition')
                    ->label('Edisi')
                    ->badge(),

                Tables\Columns\TextColumn::make('version')
                    ->label('Versi')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('template.name')
                    ->label('Template')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('rootPerson.full_name')
                    ->label('Tokoh Utama')
                    ->searchable(),

                Tables\Columns\TextColumn::make('default_max_generation')
                    ->label('Generasi'),

                Tables\Columns\IconColumn::make('show_cover')
                    ->label('Cover')
                    ->boolean(),

                Tables\Columns\IconColumn::make('show_table_of_contents')
                    ->label('Daftar Isi')
                    ->boolean(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                    ]),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publish')
                    ->date('d M Y'),

            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')

                    ->form([

                        TextInput::make('title')
                            ->label('Judul Buku')
                            ->required()
                            ->default(fn (Book $record) => $record->title.' (Copy)'),

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

                    ])

                    ->action(function (Book $record, array $data) {

                        $newBook = $record->replicate();

                        // jika ada uuid
                        if (isset($newBook->uuid)) {
                            $newBook->uuid = Str::uuid();
                        }

                        $newBook->title = $data['title'];
                        $newBook->root_person_id = $data['root_person_id'];

                        $newBook->status = 'draft';
                        $newBook->published_at = null;

                        $newBook->save();

                        // duplicate semua section
                        foreach ($record->sections as $section) {

                            $newSection = $section->replicate();

                            if (isset($newSection->uuid)) {
                                $newSection->uuid = Str::uuid();
                            }

                            $newSection->book_id = $newBook->id;

                            $newSection->save();
                        }

                        return redirect(
                            BookResource::getUrl('edit', [
                                'record' => $newBook,
                            ])
                        );
                    }),

                Tables\Actions\Action::make('preview')
                    ->label('Preview PDF')
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->visible(fn (Book $record) => $record->status === 'draft')
                    ->url(fn (Book $record) => route('books.preview', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('download')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn (Book $record) => $record->status === 'published')
                    ->url(fn (Book $record) => route('books.download', $record)),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            BookSectionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'view' => Pages\ViewBook::route('/{record}'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
