<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookTemplateResource\Pages;
use App\Filament\Resources\BookTemplateResource\RelationManagers;
use App\Models\BookTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookTemplateResource extends Resource
{
    protected static ?string $model = BookTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    // protected static ?string $navigationLabel = 'Buku';

    // protected static ?string $pluralLabel = 'Buku Template';

    // protected static ?string $modelLabel = 'Buku Template';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('blade_view')
                    ->label('Template Blade')
                    ->required()
                    ->native(false)
                    ->options([
                        'pdf.book.classic'  => 'Classic',
                        'pdf.book.modern'   => 'Modern',
                        'pdf.book.minimal'  => 'Minimal',
                        'pdf.book.premium'  => 'Premium',
                    ])
                    ->searchable()
                    ->helperText('Template Blade yang digunakan saat generate PDF.'),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Template')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('blade_view')
                    ->label('Blade View')
                    ->badge()
                    ->color('info')
                    ->copyable(),
                Tables\Columns\BooleanColumn::make('is_active')
                    ->label('Aktif'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('books_count')
                    ->counts('books')
                    ->label('Dipakai')
                    ->badge(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookTemplates::route('/'),
            'create' => Pages\CreateBookTemplate::route('/create'),
            'view' => Pages\ViewBookTemplate::route('/{record}'),
            'edit' => Pages\EditBookTemplate::route('/{record}/edit'),
        ];
    }
}
