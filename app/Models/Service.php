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
 * Katalog Layanan Publik per Bidang (MasterPlan 5.1).
 *
 * Menyimpan rincian layanan masyarakat: persyaratan, alur prosedur,
 * SLA, biaya, kontak PIC, dan template formulir (PDF opsional).
 */
class Service extends Model
{
    use Auditable, DeletesOrphanedFiles, FormatsFileSize, HasFactory;

    /**
     * Kolom penyimpan file PDF di disk `public`.
     *
     * @var array<int, string>
     */
    protected array $fileAttributes = ['form_template'];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'bidang_id',
        'short_description',
        'description',
        'requirements',
        'procedure',
        'estimated_time',
        'cost',
        'pic_name',
        'pic_contact',
        'form_template',
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
            'bidang_id' => 'integer',
            'file_size' => 'integer',
        ];
    }

    /**
     * Bidang/Sub-Bagian pemilik layanan ini.
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
     * Hanya layanan ber-status published.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Apakah template formulir benar-benar ada di disk `public`
     * (menghindari tautan rusak bila berkas dihapus tanpa update baris).
     */
    public function getHasFormTemplateAttribute(): bool
    {
        return $this->form_template !== null
            && Storage::disk('public')->exists($this->form_template);
    }

    /**
     * URL publik template formulir, null bila berkas tidak tersedia.
     */
    public function getFormTemplateUrlAttribute(): ?string
    {
        return $this->has_form_template
            ? Storage::disk('public')->url($this->form_template)
            : null;
    }
}
