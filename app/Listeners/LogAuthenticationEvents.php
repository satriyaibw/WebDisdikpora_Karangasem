<?php

namespace App\Listeners;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Log;
use Throwable;

class LogAuthenticationEvents
{
    public function handle(Login|Logout|Failed $event): void
    {
        $email = null;
        $userId = null;

        if ($event instanceof Failed) {
            $email = $event->credentials['email'] ?? null;
        } elseif (isset($event->user)) {
            $email = $event->user->email ?? null;
            $userId = $event->user->id ?? null;
        }

        try {
            AuditLog::create([
                'user_id' => $userId,
                'action' => class_basename($event),
                'model_type' => User::class,
                'model_id' => $userId,
                'old_values' => null,
                'new_values' => $email ? ['email' => $email] : null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (Throwable $e) {
            // Audit log tidak boleh memblokir login/logout — fail silent + log warning
            Log::warning('Gagal mencatat audit login', [
                'action' => class_basename($event),
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
