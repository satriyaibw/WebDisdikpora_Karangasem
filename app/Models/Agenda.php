<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Agenda dinas (MasterPlan 3.3).
 *
 * Status selesai/akan datang dikomputasi dari tanggal
 * (agenda yang tanggalnya lewat otomatis dianggap selesai).
 */
class Agenda extends Model
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'date',
        'start_time',
        'end_time',
        'location',
        'pic',
        'description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * Status penyajian agenda — dihitung dari tanggal:
     * agenda yang tanggalnya lewat otomatis dianggap selesai.
     */
    public function statusLabel(): string
    {
        return match (true) {
            $this->date->isToday() => 'Hari Ini',
            $this->date->isPast() => 'Selesai',
            default => 'Akan Datang',
        };
    }
}
