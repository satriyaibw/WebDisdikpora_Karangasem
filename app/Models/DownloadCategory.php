<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kategori berkas Pusat Unduhan (MasterPlan 5.3).
 *
 * Dikelola dinamis dari panel admin; berkas ditautkan lewat
 * `category_id` dan dikelompokkan per kategori di portal publik.
 */
class DownloadCategory extends Model
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
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
     * Berkas unduhan yang tergabung dalam kategori ini.
     */
    public function downloadFiles(): HasMany
    {
        return $this->hasMany(DownloadFile::class, 'category_id');
    }
}
