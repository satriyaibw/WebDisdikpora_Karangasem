<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\DeletesOrphanedFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Foto anggota album (MasterPlan 3.5) — tersimpan dalam format WebP.
 */
class AlbumPhoto extends Model
{
    use Auditable, DeletesOrphanedFiles, HasFactory;

    /**
     * Kolom penyimpan file foto di disk `public`.
     *
     * @var array<int, string>
     */
    protected array $fileAttributes = ['photo_path'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'album_id',
        'photo_path',
        'caption',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * Album pemilik foto.
     */
    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }
}
