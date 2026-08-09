<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\PasswordReset\RequestPasswordReset;
use App\Filament\Pages\Auth\PasswordReset\ResetPassword;
use App\Models\User;
use Filament\Notifications\Auth\ResetPassword as ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    private function getAdmin(): User
    {
        return User::where('email', 'admin@disdikpora.karangasemkab.go.id')->firstOrFail();
    }

    private function assertLastNotificationTitleMatches(string $pattern): void
    {
        $notifications = session('filament.notifications') ?? [];

        $this->assertNotEmpty($notifications, 'Tidak ada notifikasi yang terkirim.');

        $lastNotification = $notifications[array_key_last($notifications)];

        $this->assertMatchesRegularExpression(
            sprintf('#^%s$#', $pattern),
            $lastNotification['title'],
            'Judul notifikasi terakhir tidak sesuai pola.',
        );
    }

    private function identifierForEmail(string $email): string
    {
        return 'livewire-rate-limiter:'.sha1(RequestPasswordReset::class.'|request|'.mb_strtolower(trim($email)));
    }

    public function test_request_reset_page_is_accessible(): void
    {
        $this->get('/admin/password-reset/request')->assertOk();
    }

    public function test_login_page_shows_forgot_password_link(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Lupa kata sandi?');
    }

    public function test_reset_link_is_sent_to_registered_email(): void
    {
        Notification::fake();

        $admin = $this->getAdmin();

        Livewire::test(RequestPasswordReset::class)
            ->fillForm(['email' => $admin->email])
            ->call('request');

        Notification::assertSentTo(
            $admin,
            ResetPasswordNotification::class,
            fn (ResetPasswordNotification $notification): bool => str_contains(
                $notification->url,
                route('filament.admin.auth.password-reset.reset', ['email' => $admin->email], false),
            ),
        );

        $this->assertLastNotificationTitleMatches('Tautan pengaturan ulang kata sandi telah dikirim melalui email!');
    }

    public function test_unknown_email_shows_same_success_message(): void
    {
        Notification::fake();

        Livewire::test(RequestPasswordReset::class)
            ->fillForm(['email' => 'tidak-terdaftar@example.test'])
            ->call('request');

        $this->assertLastNotificationTitleMatches('Tautan pengaturan ulang kata sandi telah dikirim melalui email!');

        Notification::assertNothingSent();
    }

    public function test_reset_email_is_not_sent_to_user_without_panel_access(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        Livewire::test(RequestPasswordReset::class)
            ->fillForm(['email' => $user->email])
            ->call('request');

        $this->assertLastNotificationTitleMatches('Tautan pengaturan ulang kata sandi telah dikirim melalui email!');

        Notification::assertNothingSent();
    }

    public function test_request_reset_is_rate_limited_per_ip_after_five_attempts(): void
    {
        Notification::fake();

        foreach (range(1, 5) as $i) {
            Livewire::test(RequestPasswordReset::class)
                ->fillForm(['email' => "user{$i}@example.test"])
                ->call('request');
        }

        Livewire::test(RequestPasswordReset::class)
            ->fillForm(['email' => 'pembatas@example.test'])
            ->call('request');

        $this->assertLastNotificationTitleMatches('Terlalu banyak percobaan\. Silakan coba lagi dalam \d+ detik\.');

        Notification::assertNothingSent();
    }

    public function test_rate_limited_per_email_even_from_different_ips(): void
    {
        foreach (range(1, 5) as $_) {
            RateLimiter::hit($this->identifierForEmail('target@example.test'), 60);
        }

        Livewire::test(RequestPasswordReset::class)
            ->fillForm(['email' => 'target@example.test'])
            ->call('request');

        $this->assertLastNotificationTitleMatches('Terlalu banyak percobaan\. Silakan coba lagi dalam \d+ detik\.');
    }

    public function test_valid_token_resets_password_and_redirects_to_login(): void
    {
        $admin = $this->getAdmin();
        $token = Password::broker()->createToken($admin);
        $newPassword = 'pass-baru-987';

        Livewire::test(ResetPassword::class, ['email' => $admin->email, 'token' => $token])
            ->fillForm([
                'password' => $newPassword,
                'passwordConfirmation' => $newPassword,
            ])
            ->call('resetPassword')
            ->assertRedirect(route('filament.admin.auth.login'));

        $this->assertLastNotificationTitleMatches('Kata sandi Anda sudah direset!');

        $admin->refresh();

        $this->assertNotSame($newPassword, $admin->password);
        $this->assertTrue(Hash::check($newPassword, $admin->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $admin->email]);
    }

    public function test_token_cannot_be_reused_after_reset(): void
    {
        $admin = $this->getAdmin();
        $token = Password::broker()->createToken($admin);
        $newPassword = 'bar-baru-123';
        $oldPassword = $admin->password;

        Livewire::test(ResetPassword::class, ['email' => $admin->email, 'token' => $token])
            ->fillForm([
                'password' => $newPassword,
                'passwordConfirmation' => $newPassword,
            ])
            ->call('resetPassword');

        Livewire::test(ResetPassword::class, ['email' => $admin->email, 'token' => $token])
            ->fillForm([
                'password' => 'lain-lagi-456',
                'passwordConfirmation' => 'lain-lagi-456',
            ])
            ->call('resetPassword');

        $admin->refresh();

        $this->assertLastNotificationTitleMatches('Tautan pengaturan ulang kata sandi ini tidak valid\.');
        $this->assertTrue(Hash::check($newPassword, $admin->password));
    }

    public function test_expired_token_is_rejected(): void
    {
        $admin = $this->getAdmin();
        $token = Password::broker()->createToken($admin);

        DB::table('password_reset_tokens')
            ->where('email', $admin->email)
            ->update(['created_at' => now()->subMinutes(120)]);

        Livewire::test(ResetPassword::class, ['email' => $admin->email, 'token' => $token])
            ->fillForm([
                'password' => 'pass-lama-789',
                'passwordConfirmation' => 'pass-lama-789',
            ])
            ->call('resetPassword');

        $admin->refresh();

        $this->assertLastNotificationTitleMatches('Tautan pengaturan ulang kata sandi ini tidak valid\.');
        $this->assertFalse(Hash::check('pass-lama-789', $admin->password));
    }

    public function test_rate_limited_after_five_reset_submissions(): void
    {
        $admin = $this->getAdmin();
        $token = Password::broker()->createToken($admin);

        for ($i = 0; $i < 6; $i++) {
            Livewire::test(ResetPassword::class, ['email' => $admin->email, 'token' => $token])
                ->fillForm([
                    'password' => 'pass-lama-789',
                    'passwordConfirmation' => 'pass-lama-789',
                ])
                ->call('resetPassword');
        }

        $this->assertLastNotificationTitleMatches('Terlalu banyak percobaan\. Silakan coba lagi dalam \d+ detik\.');
    }
}
