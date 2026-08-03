<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\HasPdfUploads;
use App\Filament\Resources\SopDocumentResource\Pages;
use App\Models\SopDocument;
use App\Rules\ValidPdfFile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SopDocumentResource extends Resource
{
    use HasPdfUploads;

    protected static ?string $model = SopDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationLabel = 'Dokumen SOP';

    protected static ?string $modelLabel = 'Dokumen SOP';

    protected static ?string $pluralModelLabel = 'Dokumen SOP';

    protected static ?string $navigationGroup = 'SOP';

    protected static ?int $navigationSort = 2;

    /**
     * Batasi akses halaman sesuai permission `{modul}.{aksi}`.
     * Super Admin lolos otomatis lewat gate bypass Filament Shield.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('sop.read') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('sop.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('sop.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('sop.delete') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('sop.delete') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail SOP')
                    ->description('Metadata dokumen Standar Operasional Prosedur.')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul SOP')
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
                            ->helperText('Otomatis diisi dari judul.'),
                        Forms\Components\TextInput::make('sop_number')
                            ->label('Nomor SOP')
                            ->maxLength(255)
                            ->placeholder('Contoh: 800/001/SOP/2025'),
                        Forms\Components\DatePicker::make('issuance_date')
                            ->label('Tanggal Pengesahan')
                            ->maxDate(now()->format('Y-m-d'))
                            ->placeholder('Pilih tanggal'),
                        Forms\Components\Select::make('bidang_id')
                            ->label('Bidang/Sub-Bagian')
                            ->relationship('bidang', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi Singkat')
                            ->maxLength(2000)
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
                Forms\Components\Section::make('Berkas & Publikasi')
                    ->description('Hanya berkas PDF asli, maksimal 10 MB.')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Berkas PDF')
                            ->disk('public')
                            ->directory('lampiran/sop')
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
                            ->formatStateUsing(fn (?int $state): string => SopDocument::formatFileSize($state))
                            ->helperText('Otomatis dihitung dari berkas yang diunggah.'),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(SopDocument::statusOptions())
                            ->default(SopDocument::STATUS_DRAFT)
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
                    ->label('Judul SOP')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('bidang.name')
                    ->label('Bidang/Sub-Bagian')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('sop_number')
                    ->label('Nomor SOP')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('issuance_date')
                    ->label('Pengesahan')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('file_size')
                    ->label('Ukuran')
                    ->formatStateUsing(fn (?int $state): string => SopDocument::formatFileSize($state))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('file_path')
                    ->label('Berkas')
                    ->boolean()
                    ->true('heroicon-o-document-arrow-down', 'info')
                    ->false('heroicon-o-x-circle', 'gray')
                    ->tooltip(fn (SopDocument $record): string => filled($record->file_path) ? 'PDF tersimpan di disk' : 'Belum ada berkas'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        SopDocument::STATUS_DRAFT => 'gray',
                        SopDocument::STATUS_PUBLISHED => 'success',
                        SopDocument::STATUS_ARCHIVED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => SopDocument::statusOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(SopDocument::statusOptions()),
                Tables\Filters\SelectFilter::make('bidang_id')
                    ->label('Bidang/Sub-Bagian')
                    ->relationship('bidang', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Unduh PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (SopDocument $record) {
                        if (blank($record->file_path) || ! Storage::disk('public')->exists($record->file_path)) {
                            Notification::make()
                                ->title('Berkas tidak ditemukan di disk')
                                ->danger()
                                ->send();

                            return;
                        }

                        return Storage::disk('public')->download($record->file_path);
                    })
                    ->disabled(fn (SopDocument $record): bool => blank($record->file_path) || ! Storage::disk('public')->exists($record->file_path))
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
            'index' => Pages\ListSopDocuments::route('/'),
            'create' => Pages\CreateSopDocument::route('/create'),
            'edit' => Pages\EditSopDocument::route('/{record}/edit'),
        ];
    }
}
