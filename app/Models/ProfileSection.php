<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Seksi konten dinamis halaman Profil (MasterPlan 3.6).
 *
 * Menggantikan field tetap visi/misi/tupoksi di tabel settings:
 * admin dapat menambah seksi baru (program prioritas, sasaran program,
 * dst.) dan menatanya lewat panel admin tanpa perubahan kode.
 */
class ProfileSection extends Model
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'sort_order',
        'is_active',
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
            'is_active' => 'boolean',
        ];
    }

    /**
     * Hanya seksi yang tampil di halaman profil publik.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
