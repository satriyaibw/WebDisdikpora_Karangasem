<?php

namespace App\Filament\Pages\Auth\PasswordReset;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Exception;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Auth\ResetPassword as ResetPasswordNotification;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    protected static bool $shouldRegisterNavigation = false;

    public function request(): void
    {
        try {
            $this->rateLimit(5);
            $this->rateLimitPerEmail(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return;
        }

        $data = $this->form->getState();

        Password::broker(Filament::getAuthPasswordBroker())->sendResetLink(
            $this->getCredentialsFromFormData($data),
            function (CanResetPassword $user, string $token): void {
                if (
                    ($user instanceof FilamentUser) &&
                    (! $user->canAccessPanel(Filament::getCurrentPanel()))
                ) {
                    return;
                }

                if (! method_exists($user, 'notify')) {
                    $userClass = $user::class;

                    throw new Exception("Model [{$userClass}] does not have a [notify()] method.");
                }

                $notification = app(ResetPasswordNotification::class, ['token' => $token]);
                $notification->url = Filament::getResetPasswordUrl($token, $user);

                $user->notify($notification);

                if (class_exists(PasswordResetLinkSent::class)) {
                    event(new PasswordResetLinkSent($user));
                }
            },
        );

        // Pesan sukses seragam agar keberadaan email tidak dapat dideteksi (anti-enum).
        $this->getSentNotification(Password::RESET_LINK_SENT)?->send();

        $this->form->fill();
    }

    protected function rateLimitPerEmail(int $maxAttempts = 5, int $decaySeconds = 60): void
    {
        $email = mb_strtolower(trim((string) ($this->data['email'] ?? '')));

        if ($email === '') {
            return;
        }

        $key = 'livewire-rate-limiter:'.sha1(static::class.'|request|'.$email);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw new TooManyRequestsException(
                static::class,
                'request',
                $email,
                RateLimiter::availableIn($key),
            );
        }

        RateLimiter::hit($key, $decaySeconds);
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return Notification::make()
            ->title(__('passwords.throttled', ['seconds' => $exception->secondsUntilAvailable]))
            ->danger();
    }
}
