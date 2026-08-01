<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kategori dokumen PPID (MasterPlan 4.1):
 * Informasi Berkala, Serta Merta, dan Setiap Saat.
 */
class PpidCategory extends Model
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
        'description',
    ];

    /**
     * Dokumen PPID yang tergabung dalam kategori ini.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(PpidDocument::class, 'category_id');
    }
}
