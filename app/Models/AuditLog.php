<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * Tabel penyimpanan riwayat aktivitas admin (audit trail).
     * Ditulis otomatis oleh AuditObserver, tanpa package tambahan.
     */
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    /**
     * Kolom yang nilainya berupa JSON saat disimpan/dibaca.
     */
    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    /**
     * Pelaku aktivitas.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Label aksi dalam Bahasa Indonesia untuk tampilan panel.
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'create' => 'Buat',
            'update' => 'Ubah',
            'delete' => 'Hapus',
            default => ucfirst($this->action),
        };
    }
}
