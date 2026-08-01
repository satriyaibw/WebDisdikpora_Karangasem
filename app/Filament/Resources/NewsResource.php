<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsResource\Pages;
use App\Models\Category;
use App\Models\News;
use App\Models\User;
use App\Services\ImageOptimizer;
use Carbon\CarbonInterface;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationLabel = 'Berita & Artikel';

    protected static ?string $modelLabel = 'Berita';

    protected static ?string $pluralModelLabel = 'Berita & Artikel';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 2;

    /**
     * Batasi akses halaman sesuai permission `{modul}.{aksi}`.
     * Super Admin lolos otomatis lewat gate bypass Filament Shield.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('berita.read') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('berita.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('berita.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('berita.delete') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('berita.delete') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Konten Berita')
                    ->description('Judul, isi berita, ringkasan, dan gambar sampul.')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Components\TextInput $component, Forms\Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Otomatis diisi dari judul.'),
                        Forms\Components\RichEditor::make('content')
                            ->label('Isi Berita')
                            ->required()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('images/berita/konten')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('excerpt')
                            ->label('Ringkasan (Excerpt)')
                            ->maxLength(500)
                            ->rows(3)
                            ->helperText('Ringkasan singkat yang tampil di halaman daftar berita.'),
                        Forms\Components\FileUpload::make('cover_image')
                            ->label('Gambar Sampul')
                            ->image()
                            ->disk('public')
                            ->directory('images/berita')
                            ->imageEditor()
                            ->maxSize(20480)
                            ->saveUploadedFileUsing(fn (Forms\Components\FileUpload $component, TemporaryUploadedFile $file): string => ImageOptimizer::convertToWebp($file, 'berita'))
                            ->helperText('Otomatis dikompresi dan dikonversi ke format WebP.'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Publikasi')
                    ->description('Status, jadwal terbit, kategori, dan penulis.')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Kategori')
                            ->options(fn (): array => Category::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('author_id')
                            ->label('Penulis / Redaktur')
                            ->options(fn (): array => User::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->default(fn (): ?int => auth()->id())
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(News::statusOptions())
                            ->default(News::STATUS_DRAFT)
                            ->required()
                            ->live(),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Tanggal Terbit')
                            ->default(now())
                            ->seconds(false)
                            ->helperText('Jika status "Terbit" dan waktunya di masa depan, berita disimpan sebagai "Terjadwal" dan terbit otomatis saat waktunya tiba.'),
                        Forms\Components\TextInput::make('views_count')
                            ->label('Jumlah Dilihat')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('Sampul')
                    ->disk('public')
                    ->width(80)
                    ->height(50)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        News::STATUS_DRAFT => 'gray',
                        News::STATUS_SCHEDULED => 'warning',
                        News::STATUS_PUBLISHED => 'success',
                        News::STATUS_ARCHIVED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => News::statusOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Tanggal Terbit')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('views_count')
                    ->label('Dilihat')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('author.name')
                    ->label('Penulis')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(News::statusOptions()),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),
                Tables\Filters\Filter::make('published_at')
                    ->label('Rentang Tanggal Terbit')
                    ->form([
                        Forms\Components\DatePicker::make('published_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('published_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['published_from'], fn (Builder $query, string $date): Builder => $query->whereDate('published_at', '>=', $date))
                            ->when($data['published_until'], fn (Builder $query, string $date): Builder => $query->whereDate('published_at', '<=', $date));
                    }),
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
            ->defaultSort('published_at', 'desc');
    }

    /**
     * Logika jadwal terbit otomatis (Scheduled Publishing):
     * - Status "Terbit" + tanggal terbit di masa depan -> disimpan sebagai "Terjadwal".
     * - Status "Terjadwal" + tanggal terbit sudah lewat -> langsung "Terbit".
     * - Status "Terbit" tanpa tanggal terbit -> tanggal terbit diisi sekarang.
     *
     * Dipanggil dari halaman Create dan Edit (CreateRecord tidak
     * meneruskan ke mutateFormDataBeforeSave milik resource).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function resolvePublishStatus(array $data): array
    {
        $status = $data['status'] ?? null;
        $publishedAt = $data['published_at'] ?? null;

        if ($status === News::STATUS_PUBLISHED) {
            if (blank($publishedAt)) {
                $data['published_at'] = Carbon::now();
            } elseif (static::parsePublishTime($publishedAt)->isFuture()) {
                $data['status'] = News::STATUS_SCHEDULED;
            }
        }

        if ($status === News::STATUS_SCHEDULED) {
            if (blank($publishedAt)) {
                $data['published_at'] = Carbon::now();
                $data['status'] = News::STATUS_PUBLISHED;
            } elseif (! static::parsePublishTime($publishedAt)->isFuture()) {
                $data['status'] = News::STATUS_PUBLISHED;
            }
        }

        return $data;
    }

    /**
     * Parse nilai `published_at` dari form (string atau Carbon).
     * Nilai kosong tidak pernah sampai ke sini (sudah ditangani `blank()`).
     */
    private static function parsePublishTime(mixed $value): CarbonInterface
    {
        return $value instanceof CarbonInterface ? $value : Carbon::parse($value);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'edit' => Pages\EditNews::route('/{record}/edit'),
        ];
    }
}
