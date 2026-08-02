<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RelatedLinkResource\Pages;
use App\Models\RelatedLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RelatedLinkResource extends Resource
{
    protected static ?string $model = RelatedLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'Tautan Terkait';

    protected static ?string $modelLabel = 'Tautan Terkait';

    protected static ?string $pluralModelLabel = 'Tautan Terkait';

    protected static ?string $navigationGroup = 'Profil';

    protected static ?int $navigationSort = 4;

    /**
     * Batasi akses halaman sesuai permission `{modul}.{aksi}`.
     * Super Admin lolos otomatis lewat gate bypass Filament Shield.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('tautan.read') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('tautan.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('tautan.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('tautan.delete') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('tautan.delete') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Tautan')
                    ->description('Tautan terkait pada footer portal publik, misalnya SP4N-LAPOR!, JDIH, atau situs instansi lain. Urutan diatur lewat tabel (drag & drop).')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Tautan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('url')
                            ->label('Alamat URL')
                            ->required()
                            ->url()
                            ->maxLength(2048)
                            ->columnSpanFull()
                            ->helperText('Harus diawali http:// atau https://, misalnya https://www.lapor.go.id.'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Tampil di footer')
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Tautan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('url')
                    ->label('Alamat URL')
                    ->limit(40)
                    ->copyable()
                    ->searchable()
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
            'index' => Pages\ListRelatedLinks::route('/'),
            'create' => Pages\CreateRelatedLink::route('/create'),
            'edit' => Pages\EditRelatedLink::route('/{record}/edit'),
        ];
    }
}
