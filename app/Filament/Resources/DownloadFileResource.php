<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\HasPdfUploads;
use App\Filament\Resources\DownloadFileResource\Pages;
use App\Models\DownloadFile;
use App\Rules\ValidPdfFile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class DownloadFileResource extends Resource
{
    use HasPdfUploads;

    protected static ?string $model = DownloadFile::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationLabel = 'Berkas Unduhan';

    protected static ?string $modelLabel = 'Berkas Unduhan';

    protected static ?string $pluralModelLabel = 'Berkas Unduhan';

    protected static ?string $navigationGroup = 'Pusat Unduhan';

    protected static ?int $navigationSort = 1;

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
                Forms\Components\Section::make('Detail Berkas')
                    ->description('Informasi berkas yang tersedia di Pusat Unduhan.')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Nama Berkas')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Components\TextInput $component, Forms\Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->alphaDash()
                            ->helperText('Otomatis diisi dari nama berkas.'),
                        Forms\Components\Select::make('type')
                            ->label('Jenis Berkas')
                            ->options(DownloadFile::typeOptions())
                            ->default(DownloadFile::TYPE_FORMULIR)
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan')
                            ->maxLength(2000)
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Berkas & Publikasi')
                    ->description('Hanya berkas PDF asli, maksimal 10 MB.')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Berkas PDF')
                            ->disk('public')
                            ->directory('lampiran/unduhan')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)
                            ->rules(['mimetypes:application/pdf', new ValidPdfFile])
                            ->validationMessages([
                                'mimetypes' => 'Berkas harus berupa PDF.',
                                'max' => 'Ukuran berkas maksimal 10 MB.',
                            ])
                            ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file): string => static::safeStoredFileName($file->getClientOriginalName()))
                            ->required()
                            ->afterStateUpdated(function (Forms\Set $set, TemporaryUploadedFile|string|null $state): void {
                                $set('file_size', $state instanceof TemporaryUploadedFile ? $state->getSize() : null);
                            })
                            ->helperText('Hanya berkas PDF asli (dicek magic bytes), maksimal 10 MB.'),
                        Forms\Components\TextInput::make('file_size')
                            ->label('Ukuran Berkas')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn (?int $state): string => DownloadFile::formatFileSize($state))
                            ->helperText('Otomatis dihitung dari berkas yang diunggah.'),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(DownloadFile::statusOptions())
                            ->default(DownloadFile::STATUS_DRAFT)
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
                    ->label('Nama Berkas')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis Berkas')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        DownloadFile::TYPE_FORMULIR => 'info',
                        DownloadFile::TYPE_JUKNIS => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => DownloadFile::typeOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('file_size')
                    ->label('Ukuran')
                    ->formatStateUsing(fn (?int $state): string => DownloadFile::formatFileSize($state))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('file_path')
                    ->label('Berkas')
                    ->boolean()
                    ->true('heroicon-o-document-arrow-down', 'info')
                    ->false('heroicon-o-x-circle', 'gray')
                    ->tooltip(fn (DownloadFile $record): string => filled($record->file_path) ? 'PDF tersimpan di disk' : 'Belum ada berkas'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        DownloadFile::STATUS_DRAFT => 'gray',
                        DownloadFile::STATUS_PUBLISHED => 'success',
                        DownloadFile::STATUS_ARCHIVED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => DownloadFile::statusOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(DownloadFile::statusOptions()),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis Berkas')
                    ->options(DownloadFile::typeOptions()),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Unduh PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (DownloadFile $record): ?string => filled($record->file_path) && Storage::disk('public')->exists($record->file_path)
                        ? Storage::disk('public')->url($record->file_path)
                        : null)
                    ->disabled(fn (DownloadFile $record): bool => blank($record->file_path) || ! Storage::disk('public')->exists($record->file_path))
                    ->openUrlInNewTab()
                    ->tooltip('Unduh berkas PDF dari disk'),
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
            'index' => Pages\ListDownloadFiles::route('/'),
            'create' => Pages\CreateDownloadFile::route('/create'),
            'edit' => Pages\EditDownloadFile::route('/{record}/edit'),
        ];
    }
}
