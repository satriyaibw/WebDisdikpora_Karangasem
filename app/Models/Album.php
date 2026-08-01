<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * Album foto kegiatan (MasterPlan 3.5).
 */
class Album extends Model
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
    ];

    /**
     * Foto-foto anggota album.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(AlbumPhoto::class);
    }

    /**
     * Saat album dihapus, file foto turut dibersihkan dari disk.
     * (Hapus cascade di DB tidak memicu model event AlbumPhoto.)
     */
    protected static function booted(): void
    {
        static::deleting(static function (Album $album): void {
            foreach ($album->photos()->pluck('photo_path') as $path) {
                if (is_string($path) && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        });
    }
}
