<?php

namespace App\Filament\Resources\PersonResource\RelationManagers;

use App\Models\Marriage;
use App\Models\Person;
use Carbon\Carbon;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Exceptions\Halt;
use Filament\Tables;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SpouseRelationManager extends RelationManager
{
    protected static string $relationship = 'marriages';

    protected static ?string $modelLabel = 'Pasangan';

    protected static ?string $pluralModelLabel = 'Daftar Pasangan';

    protected static ?string $title = 'Daftar Pasangan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Tambah Data Pasangan')
                    ->icon('heroicon-o-heart')
                    ->schema([
                        // Informasi person (owner record)
                        Grid::make(2)
                            ->schema([
                                Placeholder::make('person_info')
                                    ->label(function () {
                                        $person = $this->getOwnerRecord();

                                        return $person->gender === 'male' ? 'Suami' : 'Istri';
                                    })
                                    ->content(function () {
                                        $person = $this->getOwnerRecord();

                                        return "👤 {$person->full_name} ({$person->person_code})";
                                    })
                                    ->columnSpan(1),

                                Placeholder::make('status_info')
                                    ->label('Status Saat Ini')
                                    ->content(function () {
                                        $person = $this->getOwnerRecord();

                                        if ($person->gender === 'female' && $person->hasActiveMarriage()) {
                                            return '⚠️ Masih terikat pernikahan aktif';
                                        }

                                        $count = $person->marriages()->count();

                                        return $count > 0
                                            ? "✅ Tercatat {$count} data pernikahan"
                                            : '⚠️ Belum ada data pernikahan';
                                    })
                                    ->columnSpan(1),
                            ]),

                        // Pilih Pasangan yang sudah ada dengan tombol +
                        Section::make('Pilih Pasangan yang Sudah Ada')
                            ->schema([
                                Select::make('existing_spouse_id')
                                    ->label('Cari Pasangan')
                                    ->options(function () {
                                        $person = $this->getOwnerRecord();
                                        $oppositeGender = $person->gender === 'male' ? 'female' : 'male';

                                        return Person::with('fatherRelation.parent')
                                            ->where('id', '!=', $person->id)
                                            ->where('gender', $oppositeGender)
                                            ->orderBy('full_name')
                                            ->get()
                                            ->mapWithKeys(function (Person $person) {
                                                return [
                                                    $person->id => "{$person->full_name_with_nasab} ({$person->person_code})",
                                                ];
                                            })
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->placeholder('Ketik untuk mencari pasangan...')
                                    ->helperText('Pilih pasangan yang sudah ada, atau klik tombol + untuk menambah pasangan baru')
                                    ->required()
                                    ->rules(['required', 'exists:people,id'])
                                    ->validationAttribute('pasangan')
                                    ->suffixAction(
                                        Action::make('createPerson')
                                            ->label('Tambah Pasangan Baru')
                                            ->icon('heroicon-o-plus-circle')
                                            ->color('primary')
                                            ->form([
                                                Section::make('Data Pasangan Baru')
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
                                                                    ->inline()
                                                                    ->default(function () {
                                                                        $person = $this->getOwnerRecord();

                                                                        return $person->gender === 'male' ? 'female' : 'male';
                                                                    })
                                                                    ->disabled()
                                                                    ->dehydrated(true)
                                                                    ->helperText('Jenis kelamin otomatis menyesuaikan lawan jenis dari person ini.')
                                                                    ->columnSpan(2),

                                                                TextInput::make('birth_place')
                                                                    ->label('Tempat Lahir')
                                                                    ->maxLength(255)
                                                                    ->columnSpan(1),

                                                                // Tanggal Lahir (Tahun + Bulan)
                                                                Grid::make(2)
                                                                    ->schema([
                                                                        TextInput::make('birth_year')
                                                                            ->label('Tahun Lahir')
                                                                            ->numeric()
                                                                            ->minValue(1900)
                                                                            ->maxValue(date('Y'))
                                                                            ->placeholder('contoh: 1970')
                                                                            ->columnSpan(1),

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
                                                                            ->columnSpan(1),
                                                                    ])
                                                                    ->columnSpan(1),

                                                                DatePicker::make('death_date')
                                                                    ->label('Tanggal Wafat')
                                                                    ->displayFormat('d/m/Y')
                                                                    ->columnSpan(1),
                                                            ]),
                                                    ]),
                                            ])
                                            ->action(function (array $data, Set $set) {
                                                try {
                                                    DB::beginTransaction();

                                                    $birthDate = null;
                                                    if (! empty($data['birth_year']) && ! empty($data['birth_month'])) {
                                                        $birthDate = Carbon::createFromDate(
                                                            $data['birth_year'],
                                                            $data['birth_month'],
                                                            1
                                                        )->format('Y-m-d');
                                                    }

                                                    $person = Person::create([
                                                        'full_name' => $data['full_name'],
                                                        'nickname' => $data['nickname'] ?? null,
                                                        'gender' => $data['gender'],
                                                        'birth_place' => $data['birth_place'] ?? null,
                                                        'birth_date' => $birthDate,
                                                        'death_date' => $data['death_date'] ?? null,
                                                    ]);

                                                    DB::commit();

                                                    $set('existing_spouse_id', $person->id);

                                                    Notification::make()
                                                        ->title('✅ Berhasil!')
                                                        ->body("Data pasangan {$person->full_name} berhasil ditambahkan.")
                                                        ->success()
                                                        ->send();

                                                } catch (\Exception $e) {
                                                    DB::rollBack();

                                                    Notification::make()
                                                        ->title('❌ Gagal!')
                                                        ->body($e->getMessage())
                                                        ->danger()
                                                        ->send();
                                                }
                                            })
                                            ->modalHeading('Tambah Pasangan Baru')
                                            ->modalWidth('4xl')
                                            ->modalSubmitActionLabel('Simpan')
                                    )
                                    ->columnSpanFull(),
                            ]),

                        // Data Pernikahan
                        Section::make('Data Pernikahan')
                            ->icon('heroicon-o-heart')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        DatePicker::make('marriage_date')
                                            ->label('Tanggal Menikah')
                                            ->displayFormat('d/m/Y')
                                            ->columnSpan(1),

                                        DatePicker::make('divorce_date')
                                            ->label('Tanggal Cerai (jika ada)')
                                            ->displayFormat('d/m/Y')
                                            ->helperText('Kosongkan jika masih dalam pernikahan aktif.')
                                            ->columnSpan(1),

                                        Textarea::make('notes')
                                            ->label('Catatan')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                ImageColumn::make('partner_photo')
                    ->label('Foto')
                    ->circular()
                    ->state(function ($record) {
                        $partner = $this->getPartner($record);

                        return $partner?->photo_path;
                    })
                    ->defaultImageUrl(function ($record) {
                        $partner = $this->getPartner($record);

                        return 'https://ui-avatars.com/api/?name='.urlencode($partner?->full_name ?? '?').'&color=7F9CF5&background=EBF4FF';
                    })
                    ->size(40),

                TextColumn::make('partner_code')
                    ->label('Kode')
                    ->state(fn ($record) => $this->getPartner($record)?->person_code)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('husband', fn ($q) => $q->where('person_code', 'like', "%{$search}%"))
                            ->orWhereHas('wife', fn ($q) => $q->where('person_code', 'like', "%{$search}%"));
                    })
                    ->copyable()
                    ->size('sm'),

                TextColumn::make('partner_name')
                    ->label('Nama Pasangan')
                    ->state(fn ($record) => $this->getPartner($record)?->full_name)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('husband', fn ($q) => $q->where('full_name', 'like', "%{$search}%"))
                            ->orWhereHas('wife', fn ($q) => $q->where('full_name', 'like', "%{$search}%"));
                    })
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('partner_gender')
                    ->label('JK')
                    ->state(fn ($record) => $this->getPartner($record)?->gender)
                    ->formatStateUsing(fn ($state) => $state === 'male' ? 'L' : 'P')
                    ->badge()
                    ->color(fn ($state) => $state === 'male' ? 'info' : 'danger'),

                TextColumn::make('marriage_date')
                    ->label('Tanggal Menikah')
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->translatedFormat('d F Y') : '-')
                    ->placeholder('-'),

                TextColumn::make('marriage_status')
                    ->label('Status')
                    ->state(fn ($record) => $record->divorce_date ? 'divorced' : 'active')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'divorced' ? 'Bercerai' : 'Masih Menikah')
                    ->color(fn ($state) => $state === 'divorced' ? 'danger' : 'success'),

                TextColumn::make('created_at')
                    ->label('Ditambahkan')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('divorce_date')
                    ->label('Status Pernikahan')
                    ->placeholder('Semua')
                    ->trueLabel('Bercerai')
                    ->falseLabel('Masih Menikah')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('divorce_date'),
                        false: fn (Builder $query) => $query->whereNull('divorce_date'),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Pasangan')
                    ->icon('heroicon-o-heart')
                    ->modalHeading('Tambah Pasangan')
                    ->modalWidth('4xl')
                    ->using(function (array $data, string $model): \Illuminate\Database\Eloquent\Model {
                        return $this->handleCreateSpouse($data);
                    })
                    ->beforeFormValidated(function (array $data, Tables\Actions\CreateAction $action) {
                        $person = $this->getOwnerRecord();

                        // Validasi: perempuan hanya boleh punya 1 pernikahan AKTIF
                        // (belum cerai) dalam satu waktu.
                        if ($person->gender === 'female' && $person->hasActiveMarriage()) {
                            Notification::make()
                                ->title('❌ Gagal Menambahkan Pasangan')
                                ->body('Masih terikat pernikahan aktif. Tambahkan tanggal cerai pada data pernikahan sebelumnya sebelum menambahkan pasangan baru.')
                                ->danger()
                                ->send();

                            $action->halt();
                        }

                        // Catatan: pengecekan existing_spouse_id manual sengaja
                        // tidak dilakukan di sini, sudah ditangani validasi Select.
                    }),
            ])
            ->actions([
                TableAction::make('view')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => route('filament.admin.resources.people.view', $this->getPartner($record)))
                // ->openUrlInNewTab()
                ,

                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->modalHeading('Edit Data Pernikahan')
                    ->form([
                        DatePicker::make('marriage_date')
                            ->label('Tanggal Menikah')
                            ->displayFormat('d/m/Y'),

                        DatePicker::make('divorce_date')
                            ->label('Tanggal Cerai (jika ada)')
                            ->displayFormat('d/m/Y'),

                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2),
                    ]),

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Hapus Data Pernikahan')
                    ->modalDescription('Apakah Anda yakin ingin menghapus data pernikahan ini? Data pasangan tidak akan terhapus.')
                    ->successNotificationTitle('Data pernikahan dihapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ])
            ->emptyStateHeading('Belum ada data pasangan')
            ->emptyStateDescription('Tambahkan pasangan dengan mengklik tombol "Tambah Pasangan" di atas')
            ->emptyStateIcon('heroicon-o-heart');
    }

    // Helper functions

    /**
     * Mengambil Person pasangan dari record Marriage, relatif terhadap
     * owner record (person yang sedang dibuka halaman detailnya).
     */
    private function getPartner(Marriage $record): ?Person
    {
        $ownerId = $this->getOwnerRecord()->id;

        return $record->husband_id === $ownerId ? $record->wife : $record->husband;
    }

    private function handleCreateSpouse(array $data): \Illuminate\Database\Eloquent\Model
    {
        $person = $this->getOwnerRecord();

        if (empty($data['existing_spouse_id'])) {
            throw new \Exception('Silakan pilih pasangan yang sudah ada.');
        }

        DB::beginTransaction();
        try {
            $spouse = Person::find($data['existing_spouse_id']);
            if (! $spouse) {
                throw new \Exception('Data pasangan tidak ditemukan.');
            }

            // Tentukan suami/istri berdasarkan gender person (owner record)
            $husbandId = $person->gender === 'male' ? $person->id : $spouse->id;
            $wifeId = $person->gender === 'male' ? $spouse->id : $person->id;

            // Cek apakah sudah pernah tercatat pernikahan dengan orang yang sama
            $existingMarriage = Marriage::where('husband_id', $husbandId)
                ->where('wife_id', $wifeId)
                ->first();

            if ($existingMarriage) {
                throw new \Exception("Sudah ada data pernikahan antara {$person->full_name} dan {$spouse->full_name}.");
            }

            // Validasi: perempuan hanya boleh 1 pernikahan aktif (belum cerai)
            if ($person->gender === 'female' && $person->hasActiveMarriage()) {
                throw new \Exception('Masih terikat pernikahan aktif. Tambahkan tanggal cerai pada data sebelumnya terlebih dahulu.');
            }

            // Laki-laki boleh menambahkan lebih dari 1 data pasangan tanpa batas
            $marriage = Marriage::create([
                'husband_id' => $husbandId,
                'wife_id' => $wifeId,
                'marriage_date' => $data['marriage_date'] ?? null,
                'divorce_date' => $data['divorce_date'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            DB::commit();

            Notification::make()
                ->title('✅ Berhasil menambahkan pasangan')
                ->body("Data pernikahan {$person->full_name} dengan {$spouse->full_name} berhasil ditambahkan.")
                ->success()
                ->send();

            return $marriage;

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('❌ Gagal menambahkan pasangan')
                ->body($e->getMessage())
                ->danger()
                ->send();

            // Hentikan action secara halus (modal tetap terbuka,
            // tidak memicu halaman error Laravel)
            throw new Halt;
        }
    }
}
