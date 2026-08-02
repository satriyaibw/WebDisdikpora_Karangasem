<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfficialResource\Pages;
use App\Models\Official;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OfficialResource extends Resource
{
    protected static ?string $model = Official::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Pejabat & Struktur';

    protected static ?string $modelLabel = 'Pejabat';

    protected static ?string $pluralModelLabel = 'Pejabat & Struktur';

    protected static ?string $navigationGroup = 'Profil';

    protected static ?int $navigationSort = 1;

    /**
     * Batasi akses halaman sesuai permission `{modul}.{aksi}`.
     * Super Admin lolos otomatis lewat gate bypass Filament Shield.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('official.read') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('official.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('official.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('official.delete') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('official.delete') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Pejabat')
                    ->description('Pejabat membentuk pohon struktur: Kepala Dinas → Sekretariat → Bidang. Urutan diatur lewat tabel (drag & drop).')
                    ->schema([
                        Forms\Components\TextInput::make('jabatan')
                            ->label('Jabatan')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Pejabat')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nip')
                            ->label('NIP')
                            ->nullable()
                            ->numeric()
                            ->maxLength(18)
                            ->placeholder('Opsional'),
                        Forms\Components\Select::make('parent_id')
                            ->label('Atasan')
                            ->relationship('parent', 'jabatan', ignoreRecord: true)
                            ->nullable()
                            ->searchable()
                            ->preload()
                            ->placeholder('— Tanpa Atasan (Puncak) —'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Tampil di bagan publik')
                            ->default(true),
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
                Tables\Columns\TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('parent.jabatan')
                    ->label('Atasan')
                    ->placeholder('— Puncak —')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status tampil'),
            ])
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
            'index' => Pages\ListOfficials::route('/'),
            'create' => Pages\CreateOfficial::route('/create'),
            'edit' => Pages\EditOfficial::route('/{record}/edit'),
        ];
    }
}
