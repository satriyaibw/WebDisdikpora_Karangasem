<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
