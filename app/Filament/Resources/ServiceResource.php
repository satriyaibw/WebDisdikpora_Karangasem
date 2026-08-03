<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\HasPdfUploads;
use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
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

class ServiceResource extends Resource
{
    use HasPdfUploads;

    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-lifebuoy';

    protected static ?string $navigationLabel = 'Katalog Layanan';

    protected static ?string $modelLabel = 'Layanan';

    protected static ?string $pluralModelLabel = 'Katalog Layanan';

    protected static ?string $navigationGroup = 'Layanan Publik';

    protected static ?int $navigationSort = 1;

    /**
     * Batasi akses halaman sesuai permission `{modul}.{aksi}`.
     * Super Admin lolos otomatis lewat gate bypass Filament Shield.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('layanan.read') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('layanan.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('layanan.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('layanan.delete') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('layanan.delete') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Layanan')
                    ->description('Identitas layanan masyarakat yang ditampilkan pada katalog.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Layanan')
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
                            ->helperText('Otomatis diisi dari nama.'),
                        Forms\Components\Select::make('bidang_id')
                            ->label('Bidang/Sub-Bagian')
                            ->relationship('bidang', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('short_description')
                            ->label('Ringkasan')
                            ->maxLength(255)
                            ->placeholder('Ringkasan singkat untuk kartu tampilan daftar'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Rincian Layanan')
                    ->description('Penjelasan lengkap, persyaratan, dan alur prosedur layanan.')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->label('Penjelasan Lengkap')
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('images/layanan/konten')
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('requirements')
                            ->label('Persyaratan')
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('images/layanan/konten')
                            ->helperText('Syarat-syarat yang harus dipenuhi pemohon.')
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('procedure')
                            ->label('Bagan Alur Prosedur')
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('images/layanan/konten')
                            ->helperText('Langkah-langkah alur pengurusan layanan.')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('estimated_time')
                            ->label('Estimasi Waktu (SLA)')
                            ->maxLength(255)
                            ->placeholder('Contoh: 3 Hari Kerja'),
                        Forms\Components\TextInput::make('cost')
                            ->label('Biaya')
                            ->maxLength(255)
                            ->default('Rp 0 / Gratis')
                            ->placeholder('Contoh: Rp 0 / Gratis'),
                        Forms\Components\TextInput::make('pic_name')
                            ->label('Penanggung Jawab (PIC)')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('pic_contact')
                            ->label('Kontak PIC')
                            ->maxLength(255)
                            ->placeholder('Telepon / email'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Berkas & Publikasi')
                    ->description('Template formulir (opsional) dan status publikasi.')
                    ->schema([
                        Forms\Components\FileUpload::make('form_template')
                            ->label('Template Formulir (PDF)')
                            ->disk('public')
                            ->directory('lampiran/layanan')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)
                            ->rules(['mimetypes:application/pdf', new ValidPdfFile])
                            ->validationMessages([
                                'mimetypes' => 'Berkas harus berupa PDF.',
                                'max' => 'Ukuran berkas maksimal 10 MB.',
                            ])
                            ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file): string => static::safeStoredFileName($file->getClientOriginalName()))
                            ->afterStateUpdated(function (Forms\Set $set, TemporaryUploadedFile|string|null $state): void {
                                $set('file_size', $state instanceof TemporaryUploadedFile ? $state->getSize() : null);
                            })
                            ->helperText('Opsional — hanya berkas PDF asli (dicek magic bytes), maksimal 10 MB.'),
                        Forms\Components\TextInput::make('file_size')
                            ->label('Ukuran Berkas')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn (?int $state): string => Service::formatFileSize($state))
                            ->helperText('Otomatis dihitung dari berkas yang diunggah.'),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(Service::statusOptions())
                            ->default(Service::STATUS_DRAFT)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Layanan')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('bidang.name')
                    ->label('Bidang/Sub-Bagian')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('estimated_time')
                    ->label('Estimasi Waktu')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('cost')
                    ->label('Biaya')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('form_template')
                    ->label('Formulir')
                    ->boolean()
                    ->true('heroicon-o-document-arrow-down', 'info')
                    ->false('heroicon-o-x-circle', 'gray')
                    ->tooltip(fn (Service $record): string => filled($record->form_template) ? 'Template formulir PDF tersedia' : 'Tanpa template formulir'),
                Tables\Columns\TextColumn::make('file_size')
                    ->label('Ukuran')
                    ->formatStateUsing(fn (?int $state): string => Service::formatFileSize($state))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Service::STATUS_DRAFT => 'gray',
                        Service::STATUS_PUBLISHED => 'success',
                        Service::STATUS_ARCHIVED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => Service::statusOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(Service::statusOptions()),
                Tables\Filters\SelectFilter::make('bidang_id')
                    ->label('Bidang/Sub-Bagian')
                    ->relationship('bidang', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Unduh Formulir')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Service $record) {
                        if (blank($record->form_template) || ! Storage::disk('public')->exists($record->form_template)) {
                            Notification::make()
                                ->title('Berkas tidak ditemukan di disk')
                                ->danger()
                                ->send();

                            return;
                        }

                        return Storage::disk('public')->download($record->form_template);
                    })
                    ->disabled(fn (Service $record): bool => blank($record->form_template) || ! Storage::disk('public')->exists($record->form_template))
                    ->tooltip('Unduh template formulir PDF dari disk'),
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
