<?php

namespace App\Filament\Resources\PersonResource\RelationManagers;

use App\Models\Person;
use App\Models\Marriage;
use App\Models\ParentChildRelation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Support\Exceptions\Halt;
use Carbon\Carbon;

class ChildRelationManager extends RelationManager
{
    protected static string $relationship = 'childRelations';

    protected static ?string $recordTitleAttribute = 'child.full_name';

    protected static ?string $modelLabel = 'Anak';

    protected static ?string $pluralModelLabel = 'Daftar Anak';

    protected static ?string $title = 'Daftar Anak';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Tambah Data Anak')
                    ->icon('heroicon-o-user-plus')
                    ->schema([
                        // Informasi Orang Tua
                        Grid::make(2)
                            ->schema([
                                Placeholder::make('parent_info')
                                    ->label('Orang Tua')
                                    ->content(function () {
                                        $parent = $this->getOwnerRecord();
                                        $genderLabel = $parent->gender === 'male' ? 'Ayah' : 'Ibu';
                                        return "👤 {$genderLabel}: {$parent->full_name} ({$parent->person_code})";
                                    })
                                    ->columnSpan(1),

                                Placeholder::make('spouse_info')
                                    ->label('Pasangan')
                                    ->content(function () {
                                        $parent = $this->getOwnerRecord();
                                        $spouse = $this->getSpouse($parent->id);

                                        if ($spouse) {
                                            $genderLabel = $spouse->gender === 'male' ? 'Ayah' : 'Ibu';
                                            return "👤 {$genderLabel}: {$spouse->full_name} ({$spouse->person_code})";
                                        }

                                        return '⚠️ Belum ada pasangan';
                                    })
                                    ->columnSpan(1),
                            ]),

                        // Pilih Pasangan (jika lebih dari 1 pernikahan)
                        Select::make('spouse_id')
                            ->label('Pilih Pasangan')
                            ->options(function () {
                                $parent = $this->getOwnerRecord();
                                $marriages = $this->getMarriages($parent->id);
                                $options = [];

                                foreach ($marriages as $marriage) {
                                    $spouse = $marriage->husband_id == $parent->id
                                        ? $marriage->wife
                                        : $marriage->husband;

                                    if ($spouse) {
                                        $genderLabel = $spouse->gender === 'male' ? 'Ayah' : 'Ibu';
                                        $options[$spouse->id] = "{$genderLabel}: {$spouse->full_name} ({$spouse->person_code})";
                                    }
                                }

                                return $options;
                            })
                            ->searchable()
                            ->preload()
                            ->helperText('Pilih pasangan (akan otomatis terisi jika hanya 1 pasangan)')
                            ->visible(function () {
                                $parent = $this->getOwnerRecord();
                                $marriages = $this->getMarriages($parent->id);
                                return $marriages->count() > 1;
                            }),

                        // Hidden untuk menyimpan data
                        Hidden::make('parent_id')
                            ->default(fn () => $this->getOwnerRecord()->id),

                        // Pilih Anak yang sudah ada dengan tombol +
                        Section::make('Pilih Anak yang Sudah Ada')
                            ->schema([
                                Select::make('existing_child_id')
                                    ->label('Cari Anak')
                                    ->options(function () {
                                        return Person::orderBy('full_name')
                                            ->pluck('full_name', 'id')
                                            ->map(function ($name, $id) {
                                                $person = Person::find($id);
                                                return "{$name} ({$person->person_code})";
                                            })
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->placeholder('Ketik untuk mencari anak...')
                                    ->helperText('Pilih anak yang sudah ada, atau klik tombol + untuk menambah anak baru')
                                    ->required()
                                    ->rules(['required', 'exists:people,id'])
                                    ->validationAttribute('anak')
                                    ->suffixAction(
                                        Action::make('createPerson')
                                            ->label('Tambah Anak Baru')
                                            ->icon('heroicon-o-plus-circle')
                                            ->color('primary')
                                            ->form([
                                                Section::make('Data Anak Baru')
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
                                                                            ->placeholder('contoh: 1998')
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
                                                    if (!empty($data['birth_year']) && !empty($data['birth_month'])) {
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

                                                    // Langsung set value select existing_child_id
                                                    // tanpa perlu dispatch event/listener tambahan
                                                    $set('existing_child_id', $person->id);

                                                    Notification::make()
                                                        ->title('✅ Berhasil!')
                                                        ->body("Data anak {$person->full_name} berhasil ditambahkan.")
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
                                            ->modalHeading('Tambah Anak Baru')
                                            ->modalWidth('4xl')
                                            ->modalSubmitActionLabel('Simpan')
                                    )
                                    ->columnSpanFull(),
                            ]),

                        // Jenis Hubungan
                        Section::make('Jenis Hubungan')
                            ->schema([
                                ToggleButtons::make('type')
                                    ->label('Status Hubungan')
                                    ->required()
                                    ->options([
                                        'biological' => 'Anak Kandung',
                                        'adopted' => 'Anak Angkat',
                                        'step' => 'Anak Tiri',
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
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('child.full_name')
            ->columns([
                ImageColumn::make('child.photo_path')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->child->full_name) . '&color=7F9CF5&background=EBF4FF')
                    ->size(40),

                TextColumn::make('child.person_code')
                    ->label('Kode')
                    ->searchable()
                    ->copyable()
                    ->size('sm'),

                TextColumn::make('child.full_name')
                    ->label('Nama Anak')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('child.gender')
                    ->label('JK')
                    ->formatStateUsing(fn ($state) => $state === 'male' ? 'L' : 'P')
                    ->badge()
                    ->color(fn ($state) => $state === 'male' ? 'info' : 'danger'),

                TextColumn::make('child.birth_date')
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
                    ->label('Tambah Anak')
                    ->icon('heroicon-o-user-plus')
                    ->modalHeading('Tambah Anak')
                    ->modalWidth('4xl')
                    ->using(function (array $data, string $model): \Illuminate\Database\Eloquent\Model {
                        return $this->handleCreateChild($data);
                    })
                    ->beforeFormValidated(function (array $data, Tables\Actions\CreateAction $action) {
                        // Validasi apakah person memiliki pasangan
                        $parent = $this->getOwnerRecord();
                        $marriages = $this->getMarriages($parent->id);

                        if ($marriages->count() === 0) {
                            Notification::make()
                                ->title('❌ Gagal Menambahkan Anak')
                                ->body('Person ini belum memiliki pasangan. Silakan tambahkan pernikahan terlebih dahulu.')
                                ->danger()
                                ->send();

                            $action->halt();
                        }

                        // Catatan: pengecekan existing_child_id sengaja TIDAK dilakukan di sini
                        // karena $data pada hook ini berjalan sebelum form selesai
                        // tervalidasi/dehydrate sehingga nilainya bisa tidak akurat.
                        // Validasi existing_child_id sudah ditangani oleh
                        // ->required() dan ->rules(['required', 'exists:people,id'])
                        // pada Select::make('existing_child_id') di atas.
                    }),
            ])
            ->actions([
                // Action View - mengarah ke halaman view person
                TableAction::make('view')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => route('filament.admin.resources.people.view', $record->child))
                    // ->openUrlInNewTab()
                    ,

                // Action Delete - hanya hapus relasi
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Hapus Hubungan')
                    ->modalDescription('Apakah Anda yakin ingin menghapus hubungan ini? Data anak tidak akan terhapus.')
                    ->using(function ($record) {
                        // Hapus relasi untuk kedua orang tua (ayah dan ibu)
                        $childId = $record->child_id;
                        $parentId = $this->getOwnerRecord()->id;

                        // Cari pasangan
                        $spouse = $this->getSpouse($parentId);

                        DB::beginTransaction();
                        try {
                            // Hapus relasi untuk parent
                            $record->delete();

                            // Hapus relasi untuk spouse jika ada
                            if ($spouse) {
                                ParentChildRelation::where('parent_id', $spouse->id)
                                    ->where('child_id', $childId)
                                    ->delete();
                            }

                            DB::commit();

                            Notification::make()
                                ->title('✅ Hubungan dihapus')
                                ->body('Hubungan dengan anak berhasil dihapus.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            throw $e;
                        }
                    })
                    ->successNotification(null),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->action(function ($records) {
                            $parentId = $this->getOwnerRecord()->id;
                            $spouse = $this->getSpouse($parentId);

                            DB::beginTransaction();
                            try {
                                foreach ($records as $record) {
                                    $record->delete();

                                    if ($spouse) {
                                        ParentChildRelation::where('parent_id', $spouse->id)
                                            ->where('child_id', $record->child_id)
                                            ->delete();
                                    }
                                }

                                DB::commit();

                                Notification::make()
                                    ->title('✅ Hubungan dihapus')
                                    ->body('Hubungan dengan anak terpilih berhasil dihapus.')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                DB::rollBack();
                                throw $e;
                            }
                        }),
                ]),
            ])
            ->emptyStateHeading('Belum ada data anak')
            ->emptyStateDescription('Tambahkan anak dengan mengklik tombol "Tambah Anak" di atas')
            ->emptyStateIcon('heroicon-o-user-plus');
    }

    // Helper functions
    private function getMarriages($personId): Collection
    {
        return Marriage::where('husband_id', $personId)
            ->orWhere('wife_id', $personId)
            ->whereNull('divorce_date')
            ->with(['husband', 'wife'])
            ->get();
    }

    private function getSpouse($personId)
    {
        $marriage = Marriage::where(function ($query) use ($personId) {
            $query->where('husband_id', $personId)
                ->orWhere('wife_id', $personId);
        })
        ->whereNull('divorce_date')
        ->first();

        if ($marriage) {
            return $marriage->husband_id == $personId
                ? $marriage->wife
                : $marriage->husband;
        }

        return null;
    }

    private function handleCreateChild(array $data): \Illuminate\Database\Eloquent\Model
    {
        $parent = $this->getOwnerRecord();
        $parentId = $parent->id;

        // Ambil spouse_id
        $spouseId = $data['spouse_id'] ?? null;

        if (!$spouseId) {
            // Cari pasangan dari pernikahan pertama yang aktif
            $spouse = $this->getSpouse($parentId);
            if ($spouse) {
                $spouseId = $spouse->id;
            }
        }

        // Validasi existing_child_id
        if (empty($data['existing_child_id'])) {
            throw new \Exception('Silakan pilih anak yang sudah ada.');
        }

        DB::beginTransaction();
        try {
            // Get child from existing
            $child = Person::find($data['existing_child_id']);
            if (!$child) {
                throw new \Exception('Data anak tidak ditemukan.');
            }

            // Cek apakah relasi sudah ada
            $existingRelation = ParentChildRelation::where('parent_id', $parentId)
                ->where('child_id', $child->id)
                ->first();

            if ($existingRelation) {
                throw new \Exception("Anak {$child->full_name} sudah terdaftar sebagai anak dari orang ini.");
            }

            // Create relation for parent
            $relation = ParentChildRelation::create([
                'parent_id' => $parentId,
                'child_id' => $child->id,
                'type' => $data['type'],
            ]);

            // Create relation for spouse if exists
            if ($spouseId) {
                // Cek apakah relasi dengan spouse sudah ada
                $spouseRelation = ParentChildRelation::where('parent_id', $spouseId)
                    ->where('child_id', $child->id)
                    ->first();

                if (!$spouseRelation) {
                    ParentChildRelation::create([
                        'parent_id' => $spouseId,
                        'child_id' => $child->id,
                        'type' => $data['type'],
                    ]);
                }
            }

            DB::commit();

            $spouseName = $spouseId ? Person::find($spouseId)->full_name : '';
            $parentName = $parent->full_name;

            Notification::make()
                ->title('✅ Berhasil menambahkan anak')
                ->body("Anak {$child->full_name} berhasil ditambahkan sebagai anak dari {$parentName}" . ($spouseName ? " dan {$spouseName}" : ''))
                ->success()
                ->send();

            return $relation;

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('❌ Gagal menambahkan anak')
                ->body($e->getMessage())
                ->danger()
                ->send();

            // Hentikan action secara halus (modal tetap terbuka,
            // tidak memicu halaman error Laravel)
            throw new Halt();
        }
    }
}