<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InfographicResource\Pages;
use App\Models\Infographic;
use App\Services\ImageOptimizer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class InfographicResource extends Resource
{
    protected static ?string $model = Infographic::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Infografis & Spanduk';

    protected static ?string $modelLabel = 'Infografis';

    protected static ?string $pluralModelLabel = 'Infografis & Spanduk';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 4;

    /**
     * Batasi akses halaman sesuai permission `{modul}.{aksi}`.
     * Super Admin lolos otomatis lewat gate bypass Filament Shield.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('infografis.read') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('infografis.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('infografis.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('infografis.delete') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('infografis.delete') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Materi Infografis')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Gambar Infografis')
                            ->image()
                            ->disk('public')
                            ->directory('images/infografis')
                            ->imageEditor()
                            ->saveUploadedFileUsing(fn (Forms\Components\FileUpload $component, TemporaryUploadedFile $file): string => ImageOptimizer::convertToWebp($file, 'infografis'))
                            ->required()
                            ->helperText('Otomatis dikompresi dan dikonversi ke format WebP.'),
                        Forms\Components\TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('link')
                            ->label('Tautan Tujuan (Opsional)')
                            ->url()
                            ->maxLength(500)
                            ->helperText('Saat infografis diklik, pengguna diarahkan ke tautan ini.'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
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
                    ->label('Gambar')
                    ->disk('public')
                    ->width(120)
                    ->height(70),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('link')
                    ->label('Tautan')
                    ->limit(40)
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInfographics::route('/'),
            'create' => Pages\CreateInfographic::route('/create'),
            'edit' => Pages\EditInfographic::route('/{record}/edit'),
        ];
    }
}
