<?php

namespace App\Models\Traits;

use App\Observers\AuditObserver;

/**
 * Trait untuk mendaftarkan AuditObserver otomatis pada model.
 *
 * Cukup gunakan `use Auditable;` pada model (User, Role, Permission,
 * atau model modul Fase 3+) maka seluruh Create/Update/Delete
 * akan tercatat otomatis ke tabel `audit_logs`.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::observe(AuditObserver::class);
    }
}
