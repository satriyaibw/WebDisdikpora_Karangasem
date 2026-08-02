<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\DeletesOrphanedFiles;
use App\Models\Traits\FormatsFileSize;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Dokumen Standar Operasional Prosedur (MasterPlan 5.2).
 *
 * Direktori SOP per Bidang/Sub-Bagian lengkap dengan berkas PDF,
 * nomor SOP, dan tanggal pengesahan.
 */
class SopDocument extends Model
{
    use Auditable, DeletesOrphanedFiles, FormatsFileSize, HasFactory;

    /**
     * Kolom penyimpan file PDF di disk `public`.
     *
     * @var array<int, string>
     */
    protected array $fileAttributes = ['file_path'];

    public const STATUS_DRAFT = 'draft';

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
        'sop_number',
        'issuance_date',
        'description',
        'bidang_id',
        'file_path',
        'file_size',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issuance_date' => 'date',
            'bidang_id' => 'integer',
            'file_size' => 'integer',
        ];
    }

    /**
     * Bidang/Sub-Bagian pemilik SOP ini.
     */
    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
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
            self::STATUS_PUBLISHED => 'Terbit',
            self::STATUS_ARCHIVED => 'Arsip',
        ];
    }

    /**
     * Hanya dokumen SOP ber-status published.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Apakah berkas PDF benar-benar ada di disk `public`
     * (menghindari tautan/iframe rusak bila berkas dihapus tanpa update baris).
     */
    public function getFileExistsAttribute(): bool
    {
        return $this->file_path !== null
            && Storage::disk('public')->exists($this->file_path);
    }

    /**
     * URL publik berkas PDF, null bila berkas tidak tersedia.
     */
    public function getFileUrlAttribute(): ?string
    {
        return $this->file_exists
            ? Storage::disk('public')->url($this->file_path)
            : null;
    }
}
