<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Tautan terkait pada footer portal publik (MasterPlan 6.7).
 *
 * Dikelola dinamis dari panel admin agar portal dapat menautkan situs
 * pemerintah lain (SP4N-LAPOR!, JDIH, dst.) sesuai ketentuan penautan
 * antar situs. Hanya tautan aktif yang tampil di footer.
 */
class RelatedLink extends Model
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'url',
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
     * Hanya tautan yang tampil di footer portal publik.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
