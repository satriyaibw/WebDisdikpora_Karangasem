<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\DeletesOrphanedFiles;
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
    use Auditable, DeletesOrphanedFiles, HasFactory;

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
