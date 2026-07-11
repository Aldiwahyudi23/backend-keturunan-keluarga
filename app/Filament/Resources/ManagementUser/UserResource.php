<?php

namespace App\Filament\Resources\ManagementUser;

use App\Filament\Resources\ManagementUser\UserResource\Pages;
use App\Models\User;
use App\Models\Person;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    /**
     * 🔒 FILTER QUERY (Table & Global Search)
     * 1. Hanya tampilkan user dengan type = user.
     * 2. Jika bukan Super Admin, sembunyikan user yang memiliki role 'super_admin'.
     */
    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        $query = parent::getEloquentQuery()->where('type', 'user');

        // Jika user yang login TIDAK punya role super_admin
        if (! $user->hasRole('super_admin')) {
            $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'super_admin');
            });
        }

        return $query;
    }

    /**
     * 📄 FORM
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('type')
                    ->default('user')
                    ->dehydrated(true),

                // 🔥 SELECT PERSON (DITEMBAHKAN DI ATAS)
                Forms\Components\Select::make('person_id')
                    ->label('Pilih Person')
                    ->options(function () {
                        return Person::whereDoesntHave('user', function ($query) {
                            // Pastikan hanya person yang belum punya user
                        })
                        ->orderBy('full_name')
                        ->get()
                        ->pluck('full_name_with_nasab', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->placeholder('Cari person...')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $person = Person::find($state);
                            if ($person) {
                                // Set name otomatis dari full_name person
                                $set('name', $person->full_name);
                                
                                // Opsional: set email otomatis dari full_name (bisa disesuaikan)
                                // $set('email', str()->slug($person->full_name) . '@example.com');
                            }
                        }
                    })
                    ->helperText('Pilih person yang akan dijadikan user. Nama akan otomatis terisi dari data person.'),

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->default('Mobil123'),

                Forms\Components\Select::make('roles')
                    ->relationship(
                        'roles',
                        'name',
                        /**
                         * 🔒 FILTER ROLE OPTION
                         * Jika bukan super_admin, pilihan role 'super_admin' dihilangkan dari dropdown.
                         */
                        fn (Builder $query) => Auth::user()->hasRole('super_admin')
                            ? $query
                            : $query->where('name', '!=', 'super_admin')
                    )
                    ->preload()
                    ->required(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->inline(false),
            ]);
    }

    /**
     * 📊 TABLE
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('person.full_name_with_nasab')
                    ->label('Person')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('person', function (Builder $query) use ($search) {
                            $query->where('full_name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('roles.name')->label('Roles')->badge(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Email Terverifikasi')
                    ->icon(fn ($record) => $record->email_verified_at ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn ($record) => $record->email_verified_at ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat Pada')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}