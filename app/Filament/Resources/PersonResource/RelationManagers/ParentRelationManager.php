<?php

namespace App\Filament\Resources\PersonResource\RelationManagers;

use App\Models\Marriage;
use App\Models\ParentChildRelation;
use App\Models\Person;
use Carbon\Carbon;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Exceptions\Halt;
use Filament\Tables;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ParentRelationManager extends RelationManager
{
    protected static string $relationship = 'parentRelations';

    protected static ?string $recordTitleAttribute = 'parent.full_name';

    protected static ?string $modelLabel = 'Orang Tua';

    protected static ?string $pluralModelLabel = 'Daftar Orang Tua';

    protected static ?string $title = 'Daftar Orang Tua';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Tambah Data Orang Tua')
                    ->icon('heroicon-o-user-plus')
                    ->schema([
                        // Informasi Anak (owner record)
                        Grid::make(2)
                            ->schema([
                                Placeholder::make('child_info')
                                    ->label('Anak')
                                    ->content(function () {
                                        $child = $this->getOwnerRecord();

                                        return "👤 {$child->full_name} ({$child->person_code})";
                                    })
                                    ->columnSpan(1),

                                Placeholder::make('existing_parents_info')
                                    ->label('Orang Tua Kandung Saat Ini')
                                    ->content(function () {
                                        $child = $this->getOwnerRecord();
                                        $bioParents = $this->getBiologicalParents($child->id);

                                        if ($bioParents->isEmpty()) {
                                            return '⚠️ Belum ada orang tua kandung';
                                        }

                                        return $bioParents->map(function ($relation) {
                                            $parent = $relation->parent;
                                            $genderLabel = $parent->gender === 'male' ? 'Ayah' : 'Ibu';

                                            return "👤 {$genderLabel}: {$parent->full_name}";
                                        })->implode(' | ');
                                    })
                                    ->columnSpan(1),
                            ]),

                        // Hidden untuk menyimpan data
                        Hidden::make('child_id')
                            ->default(fn () => $this->getOwnerRecord()->id),

                        // Jenis Hubungan
                        Section::make('Jenis Hubungan')
                            ->schema([
                                ToggleButtons::make('type')
                                    ->label('Status Hubungan')
                                    ->required()
                                    ->live()
                                    ->options([
                                        'biological' => 'Orang Tua Kandung',
                                        'adopted' => 'Orang Tua Angkat',
                                        'step' => 'Orang Tua Tiri',
                                    ])
                                    ->colors([
                                        'biological' => 'success',
                                        'adopted' => 'warning',
                                        'step' => 'info',
                                    ])
                                    ->icons([
                                        'biological' => 'heroicon-o-user',
                                        'adopted' => 'heroicon-o-document-duplicate',
                                        'step' => 'heroicon-o-user-group',
                                    ])
                                    ->default('biological')
                                    ->inline()
                                    ->disableOptionWhen(function (string $value) {
                                        if ($value !== 'biological') {
                                            return false;
                                        }

                                        $child = $this->getOwnerRecord();

                                        return $this->getBiologicalParentsCount($child->id) >= 2;
                                    })
                                    ->helperText(function () {
                                        $child = $this->getOwnerRecord();
                                        if ($this->getBiologicalParentsCount($child->id) >= 2) {
                                            return 'Orang tua kandung sudah lengkap (2 orang). Pilih jenis lain untuk menambah data.';
                                        }

                                        return null;
                                    })
                                    ->columnSpanFull(),
                            ]),

                        // Pilih Orang Tua yang sudah ada dengan tombol +
                        Section::make('Pilih Orang Tua yang Sudah Ada')
                            ->schema([
                                Select::make('existing_parent_id')
                                    ->label('Cari Orang Tua')
                                    ->options(function () {
                                        return Person::with('fatherRelation.parent')
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
                                    ->placeholder('Ketik untuk mencari orang tua...')
                                    ->helperText('Pilih orang tua yang sudah ada, atau klik tombol + untuk menambah orang tua baru')
                                    ->required()
                                    ->rules(['required', 'exists:people,id'])
                                    ->validationAttribute('orang tua')
                                    ->suffixAction(
                                        Action::make('createPerson')
                                            ->label('Tambah Orang Tua Baru')
                                            ->icon('heroicon-o-plus-circle')
                                            ->color('primary')
                                            ->form([
                                                Section::make('Data Orang Tua Baru')
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
                                                                    ->default(function (Get $get) {
                                                                        if ($get('type') !== 'biological') {
                                                                            return null;
                                                                        }

                                                                        $child = $this->getOwnerRecord();
                                                                        $firstParent = $this->getFirstBiologicalParent($child->id);

                                                                        if ($firstParent) {
                                                                            return $firstParent->gender === 'male' ? 'female' : 'male';
                                                                        }

                                                                        return null;
                                                                    })
                                                                    ->disabled(function (Get $get) {
                                                                        if ($get('type') !== 'biological') {
                                                                            return false;
                                                                        }

                                                                        $child = $this->getOwnerRecord();

                                                                        return $this->getFirstBiologicalParent($child->id) !== null;
                                                                    })
                                                                    ->dehydrated(true)
                                                                    ->helperText(function (Get $get) {
                                                                        if ($get('type') !== 'biological') {
                                                                            return null;
                                                                        }

                                                                        $child = $this->getOwnerRecord();
                                                                        $firstParent = $this->getFirstBiologicalParent($child->id);

                                                                        if ($firstParent) {
                                                                            return 'Jenis kelamin otomatis mengikuti lawan jenis dari orang tua kandung pertama.';
                                                                        }

                                                                        return null;
                                                                    })
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

                                                    // Format birth_date dari tahun dan bulan
                                                    $birthDate = null;
                                                    if (! empty($data['birth_year']) && ! empty($data['birth_month'])) {
                                                        $birthDate = Carbon::createFromDate(
                                                            $data['birth_year'],
                                                            $data['birth_month'],
                                                            1
                                                        )->format('Y-m-d');
                                                    }

                                                    // Buat person baru
                                                    $person = Person::create([
                                                        'full_name' => $data['full_name'],
                                                        'nickname' => $data['nickname'] ?? null,
                                                        'gender' => $data['gender'],
                                                        'birth_place' => $data['birth_place'] ?? null,
                                                        'birth_date' => $birthDate,
                                                        'death_date' => $data['death_date'] ?? null,
                                                    ]);

                                                    DB::commit();

                                                    // Langsung set value select existing_parent_id
                                                    $set('existing_parent_id', $person->id);

                                                    Notification::make()
                                                        ->title('✅ Berhasil!')
                                                        ->body("Data orang tua {$person->full_name} berhasil ditambahkan.")
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
                                            ->modalHeading('Tambah Orang Tua Baru')
                                            ->modalWidth('4xl')
                                            ->modalSubmitActionLabel('Simpan')
                                    )
                                    ->columnSpanFull(),
                            ]),

                        // Data Pernikahan - hanya muncul ketika ini akan menjadi
                        // orang tua kandung KEDUA (orang tua kandung pertama sudah ada)
                        Section::make('Data Pernikahan')
                            ->description('Catat data pernikahan antara kedua orang tua kandung.')
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
                                            ->columnSpan(1),

                                        Textarea::make('notes')
                                            ->label('Catatan')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->visible(function (Get $get) {
                                if ($get('type') !== 'biological') {
                                    return false;
                                }

                                $child = $this->getOwnerRecord();

                                return $this->getBiologicalParentsCount($child->id) === 1;
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('parent.full_name')
            ->columns([
                ImageColumn::make('parent.photo_path')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->parent->full_name).'&color=7F9CF5&background=EBF4FF')
                    ->size(40),

                TextColumn::make('parent.person_code')
                    ->label('Kode')
                    ->searchable()
                    ->copyable()
                    ->size('sm'),

                TextColumn::make('parent.full_name')
                    ->label('Nama Orang Tua')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('parent.gender')
                    ->label('JK')
                    ->formatStateUsing(fn ($state) => $state === 'male' ? 'L' : 'P')
                    ->badge()
                    ->color(fn ($state) => $state === 'male' ? 'info' : 'danger'),

                TextColumn::make('parent.birth_date')
                    ->label('Tanggal Lahir')
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->translatedFormat('F Y') : '-')
                    ->placeholder('-'),

                TextColumn::make('type_label')
                    ->label('Status Hubungan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Anak Kandung' => 'success',
                        'Anak Angkat' => 'warning',
                        'Anak Tiri' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Ditambahkan')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis Hubungan')
                    ->options(ParentChildRelation::getTypeOptions()),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Orang Tua')
                    ->icon('heroicon-o-user-plus')
                    ->modalHeading('Tambah Orang Tua')
                    ->modalWidth('4xl')
                    ->using(function (array $data, string $model): \Illuminate\Database\Eloquent\Model {
                        return $this->handleCreateParent($data);
                    })
                    ->beforeFormValidated(function (array $data, Tables\Actions\CreateAction $action) {
                        $child = $this->getOwnerRecord();
                        $type = $data['type'] ?? null;

                        // Validasi maksimal 2 orang tua kandung
                        if ($type === 'biological' && $this->getBiologicalParentsCount($child->id) >= 2) {
                            Notification::make()
                                ->title('❌ Gagal Menambahkan Orang Tua')
                                ->body('Orang tua kandung sudah lengkap (maksimal 2 orang: 1 Ayah dan 1 Ibu).')
                                ->danger()
                                ->send();

                            $action->halt();
                        }

                        // Catatan: pengecekan existing_parent_id sengaja TIDAK dilakukan
                        // di sini karena $data pada hook ini belum tentu mencerminkan
                        // state form yang sudah tervalidasi. Validasi existing_parent_id
                        // sudah ditangani oleh ->required() dan ->rules() pada Select-nya.
                    }),
            ])
            ->actions([
                // Action View - mengarah ke halaman view person
                TableAction::make('view')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => route('filament.admin.resources.people.view', $record->parent))
                // ->openUrlInNewTab()
                ,

                // Action Delete - hanya hapus relasi
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Hapus Hubungan')
                    ->modalDescription('Apakah Anda yakin ingin menghapus hubungan ini? Data orang tua tidak akan terhapus.')
                    ->successNotificationTitle('Hubungan dihapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ])
            ->emptyStateHeading('Belum ada data orang tua')
            ->emptyStateDescription('Tambahkan orang tua dengan mengklik tombol "Tambah Orang Tua" di atas')
            ->emptyStateIcon('heroicon-o-user-plus');
    }

    // Helper functions

    /**
     * Semua relasi orang tua (parentRelations) untuk seorang anak.
     */
    private function getParents($childId): Collection
    {
        return ParentChildRelation::where('child_id', $childId)
            ->with('parent')
            ->get();
    }

    /**
     * Relasi orang tua KANDUNG (type = biological) untuk seorang anak.
     */
    private function getBiologicalParents($childId): Collection
    {
        return ParentChildRelation::where('child_id', $childId)
            ->where('type', 'biological')
            ->with('parent')
            ->get();
    }

    private function getBiologicalParentsCount($childId): int
    {
        return ParentChildRelation::where('child_id', $childId)
            ->where('type', 'biological')
            ->count();
    }

    /**
     * Mengambil Person dari orang tua kandung pertama (jika sudah ada),
     * digunakan untuk mengunci gender orang tua kandung kedua.
     */
    private function getFirstBiologicalParent($childId): ?Person
    {
        $relation = ParentChildRelation::where('child_id', $childId)
            ->where('type', 'biological')
            ->with('parent')
            ->first();

        return $relation?->parent;
    }

    private function handleCreateParent(array $data): \Illuminate\Database\Eloquent\Model
    {
        $child = $this->getOwnerRecord();
        $childId = $child->id;

        $type = $data['type'] ?? null;

        if (empty($data['existing_parent_id'])) {
            throw new \Exception('Silakan pilih orang tua yang sudah ada.');
        }

        if (empty($type)) {
            throw new \Exception('Silakan pilih jenis hubungan.');
        }

        DB::beginTransaction();
        try {
            $parent = Person::find($data['existing_parent_id']);
            if (! $parent) {
                throw new \Exception('Data orang tua tidak ditemukan.');
            }

            // Cek apakah relasi sudah ada
            $existingRelation = ParentChildRelation::where('parent_id', $parent->id)
                ->where('child_id', $childId)
                ->first();

            if ($existingRelation) {
                throw new \Exception("{$parent->full_name} sudah terdaftar sebagai orang tua dari anak ini.");
            }

            $bioCount = $this->getBiologicalParentsCount($childId);

            if ($type === 'biological') {
                // Maksimal 2 orang tua kandung
                if ($bioCount >= 2) {
                    throw new \Exception('Orang tua kandung sudah lengkap (maksimal 2 orang).');
                }

                // Jika sudah ada 1 orang tua kandung, gender wajib berbeda
                if ($bioCount === 1) {
                    $firstParent = $this->getFirstBiologicalParent($childId);

                    if ($firstParent && $firstParent->gender === $parent->gender) {
                        throw new \Exception('Jenis kelamin orang tua kandung kedua harus berbeda dengan orang tua kandung pertama.');
                    }
                }
            }

            // Simpan relasi anak -> orang tua (hanya satu data relasi yang disimpan)
            $relation = ParentChildRelation::create([
                'parent_id' => $parent->id,
                'child_id' => $childId,
                'type' => $type,
            ]);

            // Jika ini adalah orang tua kandung KEDUA, simpan juga data pernikahan
            if ($type === 'biological' && $bioCount === 1) {
                $firstParent = $this->getFirstBiologicalParent($childId);

                if ($firstParent) {
                    $husband = $firstParent->gender === 'male' ? $firstParent : $parent;
                    $wife = $firstParent->gender === 'male' ? $parent : $firstParent;

                    $existingMarriage = Marriage::where('husband_id', $husband->id)
                        ->where('wife_id', $wife->id)
                        ->first();

                    if (! $existingMarriage) {
                        Marriage::create([
                            'husband_id' => $husband->id,
                            'wife_id' => $wife->id,
                            'marriage_date' => $data['marriage_date'] ?? null,
                            'divorce_date' => $data['divorce_date'] ?? null,
                            'notes' => $data['notes'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            Notification::make()
                ->title('✅ Berhasil menambahkan orang tua')
                ->body("{$parent->full_name} berhasil ditambahkan sebagai orang tua dari {$child->full_name}.")
                ->success()
                ->send();

            return $relation;

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('❌ Gagal menambahkan orang tua')
                ->body($e->getMessage())
                ->danger()
                ->send();

            // Hentikan action secara halus (modal tetap terbuka,
            // tidak memicu halaman error Laravel)
            throw new Halt;
        }
    }
}
