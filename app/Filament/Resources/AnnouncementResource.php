<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnouncementResource\Pages;
use App\Models\Announcement;
use App\Rules\ValidPdfFile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Pengumuman';

    protected static ?string $modelLabel = 'Pengumuman';

    protected static ?string $pluralModelLabel = 'Pengumuman';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 3;

    /**
     * Batasi akses halaman sesuai permission `{modul}.{aksi}`.
     * Super Admin lolos otomatis lewat gate bypass Filament Shield.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('pengumuman.read') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('pengumuman.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('pengumuman.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('pengumuman.delete') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('pengumuman.delete') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Pengumuman')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('content')
                            ->label('Isi Pengumuman')
                            ->required()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('images/pengumuman/konten')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('announcement_number')
                            ->label('Nomor Pengumuman')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('announcement_date')
                            ->label('Tanggal Pengumuman'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Lampiran & Publikasi')
                    ->schema([
                        Forms\Components\FileUpload::make('attachment_path')
                            ->label('Lampiran PDF')
                            ->disk('public')
                            ->directory('lampiran/pengumuman')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->rules(['mimetypes:application/pdf', new ValidPdfFile])
                            ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file): string => static::safeStoredFileName($file->getClientOriginalName()))
                            ->helperText('Hanya berkas PDF, maksimal 5 MB.'),
                        Forms\Components\Toggle::make('is_important')
                            ->label('Pengumuman Penting')
                            ->helperText('Pengumuman penting ditampilkan lebih menonjol di halaman depan (Fase 6).'),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(Announcement::statusOptions())
                            ->default(Announcement::STATUS_DRAFT)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('announcement_number')
                    ->label('Nomor')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\IconColumn::make('is_important')
                    ->label('Penting')
                    ->boolean(),
                Tables\Columns\IconColumn::make('attachment_path')
                    ->label('Lampiran')
                    ->icon('heroicon-o-paper-clip')
                    ->color('info')
                    ->falseIcon('heroicon-o-minus')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Announcement::STATUS_DRAFT => 'gray',
                        Announcement::STATUS_PUBLISHED => 'success',
                        Announcement::STATUS_ARCHIVED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => Announcement::statusOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('announcement_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(Announcement::statusOptions()),
                Tables\Filters\TernaryFilter::make('is_important')
                    ->label('Pengumuman Penting'),
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
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }

    /**
     * Nama file aman untuk lampiran PDF.
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
        $safeName = $safeName !== '' ? $safeName : 'lampiran';

        return Str::limit($safeName, 60, '').'-'.Str::lower(Str::random(8)).'.pdf';
    }
}
