<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoResource\Pages;
use App\Models\Video;
use App\Rules\ValidYouTubeUrl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VideoResource extends Resource
{
    protected static ?string $model = Video::class;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';

    protected static ?string $navigationLabel = 'Video';

    protected static ?string $modelLabel = 'Video';

    protected static ?string $pluralModelLabel = 'Video';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 8;

    /**
     * Batasi akses halaman sesuai permission `{modul}.{aksi}`.
     * Super Admin lolos otomatis lewat gate bypass Filament Shield.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('video.read') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('video.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('video.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('video.delete') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('video.delete') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Video')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('youtube_url')
                            ->label('Tautan YouTube')
                            ->url()
                            ->rules([new ValidYouTubeUrl])
                            ->required()
                            ->helperText('Contoh: https://www.youtube.com/watch?v=xxxx atau https://youtu.be/xxxx'),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(Video::statusOptions())
                            ->default(Video::STATUS_DRAFT)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('youtube_url')
                    ->label('Tautan YouTube')
                    ->limit(40)
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Video::STATUS_DRAFT => 'gray',
                        Video::STATUS_PUBLISHED => 'success',
                        Video::STATUS_ARCHIVED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => Video::statusOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(Video::statusOptions()),
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
            'index' => Pages\ListVideos::route('/'),
            'create' => Pages\CreateVideo::route('/create'),
            'edit' => Pages\EditVideo::route('/{record}/edit'),
        ];
    }
}
