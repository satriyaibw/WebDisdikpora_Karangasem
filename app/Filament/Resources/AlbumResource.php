<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlbumResource\Pages;
use App\Models\Album;
use App\Services\ImageOptimizer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AlbumResource extends Resource
{
    protected static ?string $model = Album::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Galeri Foto';

    protected static ?string $modelLabel = 'Album Foto';

    protected static ?string $pluralModelLabel = 'Galeri Foto';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 7;

    /**
     * Batasi akses halaman sesuai permission `{modul}.{aksi}`.
     * Super Admin lolos otomatis lewat gate bypass Filament Shield.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('galeri.read') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('galeri.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('galeri.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('galeri.delete') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('galeri.delete') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Album')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Album')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3),
                    ]),
                Forms\Components\Section::make('Foto Album')
                    ->description('Setiap foto otomatis dikompresi dan dikonversi ke format WebP.')
                    ->schema([
                        Forms\Components\Repeater::make('photos')
                            ->label('Foto')
                            ->relationship()
                            ->orderColumn('sort_order')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->defaultItems(0)
                            ->addActionLabel('Tambah Foto')
                            ->schema([
                                Forms\Components\FileUpload::make('photo_path')
                                    ->label('Foto')
                                    ->image()
                                    ->disk('public')
                                    ->directory('images/galeri')
                                    ->imageEditor()
                                    ->maxSize(20480)
                                    ->saveUploadedFileUsing(fn (Forms\Components\FileUpload $component, TemporaryUploadedFile $file): string => ImageOptimizer::convertToWebp($file, 'galeri'))
                                    ->required(),
                                Forms\Components\TextInput::make('caption')
                                    ->label('Keterangan')
                                    ->maxLength(255),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Album')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('photos_count')
                    ->label('Jumlah Foto')
                    ->counts('photos')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAlbums::route('/'),
            'create' => Pages\CreateAlbum::route('/create'),
            'edit' => Pages\EditAlbum::route('/{record}/edit'),
        ];
    }
}
