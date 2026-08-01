<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\DeletesOrphanedFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Katalog Layanan Publik per Bidang (MasterPlan 5.1).
 *
 * Menyimpan rincian layanan masyarakat: persyaratan, alur prosedur,
 * SLA, biaya, kontak PIC, dan template formulir (PDF opsional).
 */
class Service extends Model
{
    use Auditable, DeletesOrphanedFiles, HasFactory;

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
     * Format ukuran berkas (byte) menjadi bacaan manusiawi.
     */
    public static function formatFileSize(?int $bytes): string
    {
        if ($bytes === null || $bytes < 0) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $power = $bytes > 0 ? (int) floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);

        $value = $bytes / (1024 ** $power);

        return number_format($value, $power > 0 ? 1 : 0).' '.$units[$power];
    }
}
