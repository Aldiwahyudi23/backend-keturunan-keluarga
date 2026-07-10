<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonResource\Pages;
use App\Filament\Resources\PersonResource\RelationManagers;
use App\Models\Person;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid as InfolistGrid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PersonResource extends Resource
{
    protected static ?string $model = Person::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Data Person';

    protected static ?string $pluralLabel = 'Data Person';

    protected static ?string $modelLabel = 'Person';

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Pribadi')
                    ->icon('heroicon-o-user')
                    ->collapsible()
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextInput::make('full_name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                TextInput::make('nickname')
                                    ->label('Nama Panggilan')
                                    ->maxLength(100)
                                    ->columnSpanFull(),

                                ToggleButtons::make('gender')
                                    ->label('Jenis Kelamin')
                                    ->required()
                                    ->options([
                                        'male' => 'Laki-laki',
                                        'female' => 'Perempuan',
                                    ])
                                    ->colors([
                                        'male' => 'info',
                                        'female' => 'danger',
                                    ])
                                    ->icons([
                                        'male' => 'heroicon-o-user',
                                        'female' => 'heroicon-o-user',
                                    ])
                                    ->inline()
                                    ->columnSpanFull(),

                                TextInput::make('birth_place')
                                    ->label('Tempat Lahir')
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                // Fieldset untuk tanggal lahir dengan pendekatan yang benar
                                Fieldset::make('Tanggal Lahir')
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                Select::make('birth_month')
                                                    ->label('Bulan')
                                                    ->options([
                                                        '01' => 'Januari',
                                                        '02' => 'Februari',
                                                        '03' => 'Maret',
                                                        '04' => 'April',
                                                        '05' => 'Mei',
                                                        '06' => 'Juni',
                                                        '07' => 'Juli',
                                                        '08' => 'Agustus',
                                                        '09' => 'September',
                                                        '10' => 'Oktober',
                                                        '11' => 'November',
                                                        '12' => 'Desember',
                                                    ])
                                                    ->placeholder('Pilih Bulan')
                                                    ->columnSpan([
                                                        'default' => 1,
                                                        'sm' => 1,
                                                        'md' => 6,
                                                        'lg' => 6,
                                                        'xl' => 6,
                                                    ])
                                                    ->live()
                                                    ->afterStateHydrated(function ($component, $state, $record) {
                                                        // Isi nilai bulan dari data record saat edit
                                                        if ($record && $record->birth_date) {
                                                            $component->state(Carbon::parse($record->birth_date)->format('m'));
                                                        }
                                                    })
                                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                                        $month = $state;
                                                        $year = $get('birth_year');

                                                        if ($month && $year) {
                                                            $date = Carbon::createFromDate((int) $year, (int) $month, 1);
                                                            $set('birth_date', $date->format('Y-m-d'));
                                                        } elseif (! $month || ! $year) {
                                                            $set('birth_date', null);
                                                        }
                                                    }),

                                                TextInput::make('birth_year')
                                                    ->label('Tahun')
                                                    ->numeric()
                                                    ->minValue(1900)
                                                    ->maxValue(date('Y'))
                                                    ->placeholder('contoh: 1998')
                                                    ->columnSpan([
                                                        'default' => 1,
                                                        'sm' => 1,
                                                        'md' => 6,
                                                        'lg' => 6,
                                                        'xl' => 6,
                                                    ])
                                                    ->rules(['nullable', 'integer', 'min:1900', 'max:'.date('Y')])
                                                    ->live()
                                                    ->afterStateHydrated(function ($component, $state, $record) {
                                                        // Isi nilai tahun dari data record saat edit
                                                        if ($record && $record->birth_date) {
                                                            $component->state(Carbon::parse($record->birth_date)->format('Y'));
                                                        }
                                                    })
                                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                                        $year = $state;
                                                        $month = $get('birth_month');

                                                        if ($month && $year) {
                                                            $date = Carbon::createFromDate((int) $year, (int) $month, 1);
                                                            $set('birth_date', $date->format('Y-m-d'));
                                                        } elseif (! $month || ! $year) {
                                                            $set('birth_date', null);
                                                        }
                                                    }),
                                            ])
                                            ->columns([
                                                'default' => 1,
                                                'sm' => 1,
                                                'md' => 2,
                                                'lg' => 2,
                                                'xl' => 2,
                                            ]),
                                    ])
                                    ->columnSpanFull(),

                                // Hidden field untuk birth_date yang akan disimpan
                                Forms\Components\Hidden::make('birth_date')
                                    ->dehydrated(true),

                                DatePicker::make('death_date')
                                    ->label('Tanggal Wafat')
                                    ->displayFormat('d/m/Y')
                                    ->after('birth_date')
                                    ->columnSpanFull(),

                                Grid::make()
                                    ->schema([
                                        TextInput::make('person_code')
                                            ->label('Kode Person')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->columnSpan([
                                                'default' => 1,
                                                'sm' => 1,
                                                'md' => 6,
                                                'lg' => 6,
                                                'xl' => 6,
                                            ]),

                                        Placeholder::make('created_at')
                                            ->label('Dibuat Pada')
                                            ->content(fn ($record): string => $record ? $record->created_at->format('d/m/Y H:i') : '-')
                                            ->columnSpan([
                                                'default' => 1,
                                                'sm' => 1,
                                                'md' => 6,
                                                'lg' => 6,
                                                'xl' => 6,
                                            ]),
                                    ])
                                    ->columns([
                                        'default' => 1,
                                        'sm' => 1,
                                        'md' => 2,
                                        'lg' => 2,
                                        'xl' => 2,
                                    ])
                                    ->columnSpanFull(),

                                FileUpload::make('photo_path')
                                    ->label('Foto')
                                    ->image()
                                    ->imageEditor()
                                    ->circleCropper()
                                    ->directory('people-photos')
                                    ->maxSize(2048)
                                    ->columnSpanFull(),

                                Textarea::make('bio')
                                    ->label('Biografi / Catatan')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ])
                            ->columns([
                                'default' => 1,
                                'sm' => 1,
                                'md' => 2,
                                'lg' => 2,
                                'xl' => 2,
                            ]),
                    ]),

                Section::make('Riwayat Hidup')
                    ->icon('heroicon-o-clock')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Repeater::make('histories')
                            ->relationship('histories')
                            ->label('Peristiwa Penting')
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        DatePicker::make('event_date')
                                            ->label('Tanggal Peristiwa')
                                            ->required()
                                            ->displayFormat('d/m/Y')
                                            ->columnSpanFull(),

                                        TextInput::make('title')
                                            ->label('Judul Peristiwa')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull(),

                                        TextInput::make('location')
                                            ->label('Lokasi')
                                            ->maxLength(255)
                                            ->columnSpanFull(),

                                        RichEditor::make('description')
                                            ->label('Deskripsi')
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'underline',
                                                'strike',
                                                'bulletList',
                                                'orderedList',
                                                'h2',
                                                'h3',
                                                'blockquote',
                                                'alignLeft',
                                                'alignCenter',
                                                'alignRight',
                                            ])
                                            ->columnSpanFull()
                                            ->disableGrammarly()
                                            ->fileAttachmentsDirectory('history-attachments'),
                                    ])
                                    ->columns([
                                        'default' => 1,
                                        'sm' => 1,
                                        'md' => 2,
                                        'lg' => 2,
                                        'xl' => 2,
                                    ]),
                            ])
                            ->columns(1)
                            ->defaultItems(0)
                            ->collapsible()
                            ->cloneable()
                            ->orderable('sort')
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Peristiwa')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->full_name).'&color=7F9CF5&background=EBF4FF')
                    ->size(40),

                TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable()
                    ->copyable()
                    ->size('sm')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('person_code')
                    ->label('Kode')
                    ->searchable()
                    ->copyable()
                    ->size('sm')
                    ->weight('bold'),

                TextColumn::make('full_name_with_nasab')
                    ->label('Nama')
                    ->weight('bold')
                    ->searchable(query: function ($query, string $search) {
                        $query->where('full_name', 'like', "%{$search}%")
                            ->orWhereHas('fatherRelation.parent', function ($q) use ($search) {
                                $q->where('full_name', 'like', "%{$search}%");
                            });
                    }),

                TextColumn::make('gender')
                    ->label('JK')
                    ->formatStateUsing(fn ($state) => $state === 'male' ? 'L' : 'P')
                    ->badge()
                    ->color(fn ($state) => $state === 'male' ? 'info' : 'danger'),

                TextColumn::make('birth_date')
                    ->label('Lahir')
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->translatedFormat('F Y') : '-')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('death_date')
                    ->label('Wafat')
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->translatedFormat('F Y') : '-')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('birth_place')
                    ->label('Tempat Lahir')
                    ->searchable()
                    ->limit(15)
                    ->toggleable(),

                TextColumn::make('histories_count')
                    ->label('Riwayat')
                    ->counts('histories')
                    ->badge()
                    ->color('warning')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                    ]),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Detail'),

                    Tables\Actions\EditAction::make()
                        ->label('Edit Person'),

                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('full_name', 'asc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('')
                    ->schema([
                        InfolistGrid::make(3)
                            ->schema([
                                InfolistGrid::make(1)
                                    ->schema([
                                        ImageEntry::make('photo_path')
                                            ->label('')
                                            ->circular()
                                            ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->full_name).'&color=7F9CF5&background=EBF4FF')
                                            ->height(200)
                                            ->width(200)
                                            ->extraAttributes(['class' => 'mx-auto']),
                                    ])
                                    ->columnSpan(1),

                                InfolistGrid::make(1)
                                    ->schema([
                                        TextEntry::make('full_name')
                                            ->label('')
                                            ->weight('bold')
                                            ->size('2xl')
                                            ->extraAttributes(['class' => 'text-center']),

                                        TextEntry::make('person_code')
                                            ->label('Kode Person')
                                            ->badge()
                                            ->color('info')
                                            ->extraAttributes(['class' => 'text-center']),

                                        TextEntry::make('gender')
                                            ->label('Jenis Kelamin')
                                            ->formatStateUsing(fn ($state) => $state === 'male' ? '👨 Laki-laki' : '👩 Perempuan')
                                            ->badge()
                                            ->color(fn ($state) => $state === 'male' ? 'info' : 'danger')
                                            ->extraAttributes(['class' => 'text-center']),
                                    ])
                                    ->columnSpan(1),

                                InfolistGrid::make(1)
                                    ->schema([
                                        TextEntry::make('birth_place')
                                            ->label('Tempat Lahir')
                                            ->icon('heroicon-o-map-pin'),

                                        TextEntry::make('birth_date')
                                            ->label('Tanggal Lahir')
                                            ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->translatedFormat('F Y') : '-')
                                            ->icon('heroicon-o-calendar'),

                                        TextEntry::make('death_date')
                                            ->label('Tanggal Wafat')
                                            ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->translatedFormat('d F Y') : '-')
                                            ->icon('heroicon-o-calendar')
                                            ->color(fn ($state) => $state ? 'danger' : ''),
                                    ])
                                    ->columnSpan(1),
                            ]),
                    ]),

                InfolistSection::make('Biografi')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make('bio')
                            ->label('')
                            ->html()
                            ->limit(500)
                            ->extraAttributes(['class' => 'prose max-w-none']),
                    ])
                    ->collapsible(),

                InfolistSection::make('Riwayat Hidup')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        RepeatableEntry::make('histories')
                            ->label('')
                            ->schema([
                                InfolistGrid::make(12)
                                    ->schema([
                                        InfolistGrid::make(1)
                                            ->schema([
                                                TextEntry::make('event_date')
                                                    ->label('')
                                                    ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('d F Y'))
                                                    ->weight('bold')
                                                    ->color('primary')
                                                    ->extraAttributes(['class' => 'text-right']),
                                            ])
                                            ->columnSpan(3),

                                        InfolistGrid::make(1)
                                            ->schema([
                                                TextEntry::make('divider')
                                                    ->label('')
                                                    ->formatStateUsing(fn () => '│')
                                                    ->extraAttributes(['class' => 'text-center text-gray-300 text-2xl']),
                                            ])
                                            ->columnSpan(1),

                                        InfolistGrid::make(1)
                                            ->schema([
                                                TextEntry::make('title')
                                                    ->label('')
                                                    ->weight('bold')
                                                    ->size('lg'),

                                                TextEntry::make('location')
                                                    ->label('')
                                                    ->formatStateUsing(fn ($state) => $state ? "📍 {$state}" : ''),

                                                TextEntry::make('description')
                                                    ->label('')
                                                    ->html()
                                                    ->extraAttributes(['class' => 'prose max-w-none']),
                                            ])
                                            ->columnSpan(8),
                                    ])
                                    ->extraAttributes(['class' => 'border-b border-gray-100 py-4']),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SpouseRelationManager::class,
            RelationManagers\ChildRelationManager::class,
            RelationManagers\ParentRelationManager::class,
            RelationManagers\PersonActivityRelationManager::class,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'full_name',
            'nickname',
            'person_code',
            'birth_place',
        ];
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->full_name.' ('.$record->person_code.')';
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Kode' => $record->person_code,
            'Jenis Kelamin' => $record->gender === 'male' ? 'Laki-laki' : 'Perempuan',
            'Tempat Lahir' => $record->birth_place ?? '-',
            'Tanggal Lahir' => $record->birth_date ? Carbon::parse($record->birth_date)->translatedFormat('F Y') : '-',
        ];
    }

    public static function getGlobalSearchResultUrl($record): string
    {
        return route('filament.admin.resources.people.view', $record);
    }

    public static function getGlobalSearchResultsQuery($query)
    {
        return $query->limit(5);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeople::route('/'),
            'create' => Pages\CreatePerson::route('/create'),
            'view' => Pages\ViewPerson::route('/{record}'),
            'edit' => Pages\EditPerson::route('/{record}/edit'),
        ];
    }
}
