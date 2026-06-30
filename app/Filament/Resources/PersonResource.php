<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonResource\Pages;
use App\Filament\Resources\PersonResource\RelationManagers;
use App\Filament\Resources\PersonResource\RelationManagers\ParentRelationManager;
use App\Models\Person;
use App\Models\PersonHistory;
use App\Services\PersonCardService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\ActionGroup;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Grid as InfolistGrid;
use Carbon\Carbon;

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
                        Grid::make(2)
                            ->schema([
                                TextInput::make('full_name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),
                                
                                TextInput::make('nickname')
                                    ->label('Nama Panggilan')
                                    ->maxLength(100)
                                    ->columnSpan(1),
                                
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
                                    ->columnSpan(2),
                                
                                TextInput::make('birth_place')
                                    ->label('Tempat Lahir')
                                    ->maxLength(255)
                                    ->columnSpan(1),
                                
                                // Custom input untuk tanggal lahir (hanya tahun dan bulan)
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('birth_year')
                                            ->label('Tahun Lahir')
                                            ->numeric()
                                            ->minValue(1900)
                                            ->maxValue(date('Y'))
                                            ->placeholder('contoh: 1998')
                                            ->columnSpan(1)
                                            ->default(fn ($record) => $record?->birth_date?->format('Y')),
                                        
                                        Select::make('birth_month')
                                            ->label('Bulan Lahir')
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
                                            ->columnSpan(1)
                                            ->default(fn ($record) => $record?->birth_date?->format('m')),
                                    ])
                                    ->columnSpan(1),
                                
                                DatePicker::make('death_date')
                                    ->label('Tanggal Wafat')
                                    ->displayFormat('d/m/Y')
                                    ->after('birth_date')
                                    ->columnSpan(1),
                                
                                TextInput::make('person_code')
                                    ->label('Kode Person')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpan(1),
                                
                                Placeholder::make('created_at')
                                    ->label('Dibuat Pada')
                                    ->content(fn ($record): string => $record ? $record->created_at->format('d/m/Y H:i') : '-')
                                    ->columnSpan(1),
                            ]),
                        
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
                                Grid::make(2)
                                    ->schema([
                                        DatePicker::make('event_date')
                                            ->label('Tanggal Peristiwa')
                                            ->required()
                                            ->displayFormat('d/m/Y')
                                            ->columnSpan(1),
                                        
                                        TextInput::make('title')
                                            ->label('Judul Peristiwa')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpan(1),
                                        
                                        TextInput::make('location')
                                            ->label('Lokasi')
                                            ->maxLength(255)
                                            ->columnSpan(1),
                                        
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
                                            ->columnSpan(1)
                                            ->disableGrammarly()
                                            ->fileAttachmentsDirectory('history-attachments'),
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
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->full_name) . '&color=7F9CF5&background=EBF4FF')
                    ->size(40),
                
                TextColumn::make('person_code')
                    ->label('Kode')
                    ->searchable()
                    ->copyable()
                    ->size('sm')
                    ->weight('bold'),
                
                TextColumn::make('full_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
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
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Detail'),

                     // Action Download Kartu
                    Tables\Actions\Action::make('downloadCard')
                        ->label('Download Kartu')
                        ->icon('heroicon-o-identification')
                        ->color('success')
                        ->action(function (Person $record) {
                            $service = new PersonCardService();
                            return $service->generateCard($record, true);
                        }),
                    
                    // Action Lihat Kartu (Stream)
                    Tables\Actions\Action::make('viewCard')
                        ->label('Lihat Kartu')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn (Person $record) => route('filament.admin.resources.people.card', $record))
                        ->openUrlInNewTab(),
                    
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
                ]),
            ])
            ->defaultSort('full_name', 'asc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Layout yang berbeda dengan form
                InfolistSection::make('')
                    ->schema([
                        InfolistGrid::make(3)
                            ->schema([
                                // Kolom 1: Foto
                                InfolistGrid::make(1)
                                    ->schema([
                                        ImageEntry::make('photo_path')
                                            ->label('')
                                            ->circular()
                                            ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->full_name) . '&color=7F9CF5&background=EBF4FF')
                                            ->height(200)
                                            ->width(200)
                                            ->extraAttributes(['class' => 'mx-auto']),
                                    ])
                                    ->columnSpan(1),
                                
                                // Kolom 2: Informasi Utama
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
                                
                                // Kolom 3: Detail Kelahiran & Kematian
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
                
                // Bio dengan style berbeda
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
                
                // Riwayat Hidup dengan tampilan timeline
                InfolistSection::make('Riwayat Hidup')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        RepeatableEntry::make('histories')
                            ->label('')
                            ->schema([
                                InfolistGrid::make(12)
                                    ->schema([
                                        // Tanggal di kiri
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
                                        
                                        // Garis pemisah
                                        InfolistGrid::make(1)
                                            ->schema([
                                                TextEntry::make('divider')
                                                    ->label('')
                                                    ->formatStateUsing(fn () => '│')
                                                    ->extraAttributes(['class' => 'text-center text-gray-300 text-2xl']),
                                            ])
                                            ->columnSpan(1),
                                        
                                        // Konten di kanan
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
        ];
    }

        /**
     * Definisi kolom yang bisa dicari di global search
     */
    public static function getGloballySearchableAttributes(): array
    {
        return [
            'full_name',
            'nickname',
            'person_code',
            'birth_place',
        ];
    }
    
    /**
     * Tentukan label untuk hasil pencarian
     */
    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->full_name . ' (' . $record->person_code . ')';
    }
    
    /**
     * Tentukan detail yang ditampilkan di hasil pencarian
     */
    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Kode' => $record->person_code,
            'Jenis Kelamin' => $record->gender === 'male' ? 'Laki-laki' : 'Perempuan',
            'Tempat Lahir' => $record->birth_place ?? '-',
            'Tanggal Lahir' => $record->birth_date ? Carbon::parse($record->birth_date)->translatedFormat('F Y') : '-',
        ];
    }
    
    /**
     * Tentukan URL yang dituju saat hasil pencarian diklik
     */
    public static function getGlobalSearchResultUrl($record): string
    {
        return route('filament.admin.resources.people.view', $record);
    }
    
    /**
     * Tentukan urutan hasil pencarian
     */
    public static function getGlobalSearchResultsQuery($query)
    {
        return $query->limit(5);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeople::route('/'),
            'create' => Pages\CreatePerson::route('/create'),
            'view' => Pages\ViewPerson::route('/{record}'),
            'edit' => Pages\EditPerson::route('/{record}/edit'),
            'card' => Pages\ViewPersonCard::route('/{record}/card'),
        ];
    }
}