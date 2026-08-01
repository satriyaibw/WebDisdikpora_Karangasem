<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PpidDocumentResource\Pages;
use App\Models\PpidDocument;
use App\Rules\ValidPdfFile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class PpidDocumentResource extends Resource
{
    protected static ?string $model = PpidDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Dokumen PPID';

    protected static ?string $modelLabel = 'Dokumen PPID';

    protected static ?string $pluralModelLabel = 'Dokumen PPID';

    protected static ?string $navigationGroup = 'Informasi PPID';

    protected static ?int $navigationSort = 2;

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
                Forms\Components\Section::make('Detail Dokumen')
                    ->description('Informasi metadata dokumen KIP.')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Dokumen')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('doc_number')
                            ->label('Nomor Dokumen')
                            ->maxLength(255)
                            ->placeholder('Contoh: 800/001/DISDIKPORA'),
                        Forms\Components\Select::make('year')
                            ->label('Tahun Terbit')
                            ->options(fn (): array => self::yearOptions())
                            ->searchable()
                            ->placeholder('Pilih tahun'),
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
                            ->directory('lampiran/ppid')
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
                                if ($state instanceof TemporaryUploadedFile) {
                                    $set('file_size', $state->getSize());
                                }
                            })
                            ->helperText('Hanya berkas PDF asli (dicek magic bytes), maksimal 10 MB.'),
                        Forms\Components\TextInput::make('file_size')
                            ->label('Ukuran Berkas')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn (?int $state): string => PpidDocument::formatFileSize($state))
                            ->helperText('Otomatis dihitung dari berkas yang diunggah.'),
                        Forms\Components\Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(PpidDocument::statusOptions())
                            ->default(PpidDocument::STATUS_DRAFT)
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
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('doc_number')
                    ->label('Nomor Dokumen')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('file_size')
                    ->label('Ukuran')
                    ->formatStateUsing(fn (?int $state): string => PpidDocument::formatFileSize($state))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('file_path')
                    ->label('Berkas')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->tooltip('PDF tersimpan di disk'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        PpidDocument::STATUS_DRAFT => 'gray',
                        PpidDocument::STATUS_PUBLISHED => 'success',
                        PpidDocument::STATUS_ARCHIVED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => PpidDocument::statusOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(PpidDocument::statusOptions()),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('year')
                    ->label('Tahun')
                    ->options(fn (): array => self::yearOptions()),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Unduh PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (PpidDocument $record): ?string => Storage::disk('public')->exists($record->file_path)
                        ? Storage::disk('public')->url($record->file_path)
                        : null)
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
            'index' => Pages\ListPpidDocuments::route('/'),
            'create' => Pages\CreatePpidDocument::route('/create'),
            'edit' => Pages\EditPpidDocument::route('/{record}/edit'),
        ];
    }

    /**
     * Ukuran berkas (byte) dari path di disk `public`, atau null bila tidak terbaca.
     */
    public static function resolveStoredFileSize(string $path): ?int
    {
        try {
            $size = Storage::disk('public')->size($path);
        } catch (\Throwable) {
            return null;
        }

        return $size === false ? null : $size;
    }

    /**
     * Nama file aman untuk dokumen PDF.
     *
     * Nama asli dipertahankan (dibersihkan dari segmen path & karakter
     * berbahaya) lalu diberi suffix acak agar unik di disk. Ekstensi
     * SELALU dipaksa `.pdf` — ekstensi dari client tidak dipercaya agar
     * file polyglot ber-ekstensi berbahaya (mis. `.php`) tidak dapat
     * dieksekusi web server.
     */
    public static function safeStoredFileName(string $originalName): string
    {
        $safeName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $safeName = $safeName !== '' ? $safeName : 'dokumen';

        return Str::limit($safeName, 60, '').'-'.Str::lower(Str::random(8)).'.pdf';
    }

    /**
     * Opsi tahun untuk form & filter (1990 s/d tahun depan).
     *
     * @return array<int, int>
     */
    private static function yearOptions(): array
    {
        $start = 1990;
        $end = (int) date('Y') + 1;

        return array_combine(range($start, $end), range($start, $end));
    }
}
