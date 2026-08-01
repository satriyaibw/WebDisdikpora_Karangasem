<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SliderResource\Pages;
use App\Models\Slider;
use App\Services\ImageOptimizer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SliderResource extends Resource
{
    protected static ?string $model = Slider::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';

    protected static ?string $navigationLabel = 'Hero Slider';

    protected static ?string $modelLabel = 'Hero Slider';

    protected static ?string $pluralModelLabel = 'Hero Slider';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 6;

    /**
     * Batasi akses halaman sesuai permission `{modul}.{aksi}`.
     * Super Admin lolos otomatis lewat gate bypass Filament Shield.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('slider.read') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('slider.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('slider.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('slider.delete') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('slider.delete') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Banner & CTA')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Gambar Banner')
                            ->image()
                            ->disk('public')
                            ->directory('images/slider')
                            ->imageEditor()
                            ->saveUploadedFileUsing(fn (Forms\Components\FileUpload $component, TemporaryUploadedFile $file): string => ImageOptimizer::convertToWebp($file, 'slider'))
                            ->required()
                            ->helperText('Otomatis dikompresi dan dikonversi ke format WebP.'),
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Singkat')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('Teks / Deskripsi CTA')
                            ->maxLength(500)
                            ->rows(2),
                        Forms\Components\TextInput::make('link')
                            ->label('Tautan CTA')
                            ->url()
                            ->maxLength(500),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0)
                            ->helperText('Semakin kecil angkanya, semakin awal ditampilkan. Urutan juga bisa diatur lewat drag & drop di daftar.'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->helperText('Hanya banner aktif yang dikonsumsi frontend (Fase 6).')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Banner')
                    ->disk('public')
                    ->width(120)
                    ->height(60),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
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
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSliders::route('/'),
            'create' => Pages\CreateSlider::route('/create'),
            'edit' => Pages\EditSlider::route('/{record}/edit'),
        ];
    }
}
