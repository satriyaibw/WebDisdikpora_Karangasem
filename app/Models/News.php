<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\DeletesOrphanedFiles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * Berita & artikel (MasterPlan 3.1).
 *
 * Mendukung status Draft / Terjadwal / Terbit / Arsip serta
 * penjadwalan publikasi otomatis via `published_at`.
 */
class News extends Model
{
    use Auditable, DeletesOrphanedFiles, HasFactory;

    /**
     * Kolom penyimpan file gambar di disk `public`
     * (dibersihkan otomatis saat hapus/ganti gambar).
     *
     * @var array<int, string>
     */
    protected array $fileAttributes = ['cover_image'];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'cover_image',
        'category_id',
        'status',
        'published_at',
        'author_id',
        'views_count',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'views_count' => 'integer',
        ];
    }

    /**
     * Kategori berita.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Penulis / redaktur.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Tambah jumlah dilihat (dipakai frontend Fase 6).
     *
     * Dibatasi satu kenaikan per berita per 24 jam lewat cache,
     * agar halaman yang di-refresh berulang / bot tidak menggelembungkan angka.
     */
    public function recordView(): void
    {
        $cacheKey = 'news-viewed-'.$this->getKey();

        if (Cache::has($cacheKey)) {
            return;
        }

        $this->increment('views_count');
        Cache::put($cacheKey, true, now()->addDay());
    }

    /**
     * Opsi status untuk form & filter panel admin.
     *
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SCHEDULED => 'Terjadwal',
            self::STATUS_PUBLISHED => 'Terbit',
            self::STATUS_ARCHIVED => 'Arsip',
        ];
    }

    /**
     * Hanya berita ber-status published (dengan jadwal `published_at`).
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PUBLISHED)
            ->where(fn (Builder $q) => $q
                ->whereNull('published_at')
                ->orWhere('published_at', '<=', now()));
    }

    /**
     * Apakah berita ini boleh tampil di portal publik
     * (sama dengan scopePublished, untuk guard halaman detail).
     */
    public function isPublishedForPublic(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && ($this->published_at === null || $this->published_at->lte(now()));
    }
}
