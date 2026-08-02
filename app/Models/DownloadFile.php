<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\DeletesOrphanedFiles;
use App\Models\Traits\FormatsFileSize;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Pusat Unduhan Berkas (MasterPlan 5.3).
 *
 * Formulir resmi & Petunjuk Teknis (Juknis) yang sering dibutuhkan
 * sekolah/masyarakat, dikelompokkan berdasarkan jenis berkas.
 */
class DownloadFile extends Model
{
    use Auditable, DeletesOrphanedFiles, FormatsFileSize, HasFactory;

    /**
     * Kolom penyimpan file PDF di disk `public`.
     *
     * @var array<int, string>
     */
    protected array $fileAttributes = ['file_path'];

    public const TYPE_FORMULIR = 'formulir';

    public const TYPE_JUKNIS = 'juknis';

    public const TYPE_LAINNYA = 'lainnya';

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
        'description',
        'type',
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
            'file_size' => 'integer',
        ];
    }

    /**
     * Opsi jenis berkas untuk form & filter panel admin.
     *
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return [
            self::TYPE_FORMULIR => 'Formulir',
            self::TYPE_JUKNIS => 'Petunjuk Teknis (Juknis)',
            self::TYPE_LAINNYA => 'Lainnya',
        ];
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
     * Hanya berkas unduhan ber-status published.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }
}
