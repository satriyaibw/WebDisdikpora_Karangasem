<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Observer generik pencatatan aktivitas admin (audit trail).
 *
 * Merekam aksi Create, Update, Delete beserta data lama/baru,
 * IP address, dan user-agent ke tabel `audit_logs`.
 */
class AuditObserver
{
    /**
     * Atribut yang tidak boleh ikut direkam (data sensitif).
     */
    protected const HIDDEN_ATTRIBUTES = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Aksi Create.
     */
    public function created(Model $model): void
    {
        $this->record($model, 'create', null, $model->getAttributes());
    }

    /**
     * Aksi Update (hanya atribut yang benar-benar berubah).
     */
    public function updated(Model $model): void
    {
        if (empty($model->getChanges())) {
            return;
        }

        $oldValues = [];
        $newValues = [];

        foreach (array_keys($model->getChanges()) as $attribute) {
            $oldValues[$attribute] = $model->getOriginal($attribute);
            $newValues[$attribute] = $model->getAttribute($attribute);
        }

        $this->record($model, 'update', $oldValues, $newValues);
    }

    /**
     * Aksi Delete (simpan kondisi terakhir sebelum terhapus).
     */
    public function deleted(Model $model): void
    {
        $this->record($model, 'delete', $model->getAttributes(), null);
    }

    /**
     * Tulis baris ke tabel audit_logs.
     */
    protected function record(Model $model, string $action, ?array $oldValues, ?array $newValues): void
    {
        $user = Auth::user();

        AuditLog::create([
            'user_id' => $user?->getKey(),
            'action' => $action,
            'model_type' => $model::class,
            'model_id' => $model->getKey(),
            'old_values' => $oldValues !== null ? $this->sanitize($oldValues) : null,
            'new_values' => $newValues !== null ? $this->sanitize($newValues) : null,
            'ip_address' => $this->resolveIpAddress(),
            'user_agent' => $this->resolveUserAgent(),
        ]);
    }

    /**
     * Hapus atribut sensitif dari nilai yang direkam.
     */
    protected function sanitize(array $values): array
    {
        foreach (static::HIDDEN_ATTRIBUTES as $attribute) {
            unset($values[$attribute]);
        }

        return $values;
    }

    /**
     * IP address pengguna.
     * Saat berjalan lewat CLI/queue, request kosong sehingga bernilai null.
     */
    protected function resolveIpAddress(): ?string
    {
        return request()->ip();
    }

    /**
     * User-agent pengguna.
     * Saat berjalan lewat CLI/queue, request kosong sehingga bernilai null.
     */
    protected function resolveUserAgent(): ?string
    {
        $userAgent = request()->userAgent();

        return filled($userAgent) ? substr((string) $userAgent, 0, 500) : null;
    }
}
