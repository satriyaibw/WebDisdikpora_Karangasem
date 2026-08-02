<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PpidCategoryResource\Pages;
use App\Models\PpidCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PpidCategoryResource extends Resource
{
    protected static ?string $model = PpidCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationLabel = 'Kategori Dokumen';

    protected static ?string $modelLabel = 'Kategori Dokumen';

    protected static ?string $pluralModelLabel = 'Kategori Dokumen';

    protected static ?string $navigationGroup = 'Informasi PPID';

    protected static ?int $navigationSort = 1;

    /**
     * Batasi akses halaman sesuai permission `{modul}.{aksi}`.
     * Super Admin lolos otomatis lewat gate bypass Filament Shield.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('ppid.read') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('ppid.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('ppid.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('ppid.delete') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('ppid.delete') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Kategori')
                    ->description('Kategori KIP: Informasi Berkala, Serta Merta, dan Setiap Saat (UU KIP No. 14 Tahun 2008).')
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
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->maxLength(1000)
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('documents_count')
                    ->label('Jumlah Dokumen')
                    ->counts('documents')
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
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPpidCategories::route('/'),
            'create' => Pages\CreatePpidCategory::route('/create'),
            'edit' => Pages\EditPpidCategory::route('/{record}/edit'),
        ];
    }
}
