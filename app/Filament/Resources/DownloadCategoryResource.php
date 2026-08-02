<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DownloadCategoryResource\Pages;
use App\Models\DownloadCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class DownloadCategoryResource extends Resource
{
    protected static ?string $model = DownloadCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationLabel = 'Kategori Berkas';

    protected static ?string $modelLabel = 'Kategori Berkas';

    protected static ?string $pluralModelLabel = 'Kategori Berkas';

    protected static ?string $navigationGroup = 'Pusat Unduhan';

    protected static ?int $navigationSort = 2;

    /**
     * Batasi akses halaman sesuai permission `{modul}.{aksi}`.
     * Super Admin lolos otomatis lewat gate bypass Filament Shield.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('unduhan.read') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('unduhan.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('unduhan.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('unduhan.delete') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('unduhan.delete') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Kategori')
                    ->description('Kategori berkas pada Pusat Unduhan, misalnya Formulir, Juknis, atau Peraturan.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Kategori')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Components\TextInput $component, Forms\Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->regex('/^[a-z0-9][a-z0-9-]*$/')
                            ->helperText('Otomatis diisi dari nama. Hanya huruf kecil, angka, dan tanda hubung.'),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('Urutan tampil di portal publik (kecil lebih dulu).'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('download_files_count')
                    ->label('Jumlah Berkas')
                    ->counts('downloadFiles')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDownloadCategories::route('/'),
            'create' => Pages\CreateDownloadCategory::route('/create'),
            'edit' => Pages\EditDownloadCategory::route('/{record}/edit'),
        ];
    }
}
