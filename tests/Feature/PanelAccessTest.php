<?php

namespace Tests\Feature;

use App\Filament\Resources\AuditLogResource;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_admin_login_page_is_accessible(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_user_without_role_cannot_access_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_inactive_user_cannot_access_panel(): void
    {
        $user = User::factory()->create(['is_active' => false]);
        $user->assignRole('admin_redaksi_berita');

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_user_with_valid_role_can_access_panel(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin_redaksi_berita');

        $this->actingAs($user)->get('/admin')->assertOk();
    }

    public function test_super_admin_can_access_panel(): void
    {
        $admin = $this->getSeededAdmin();

        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_super_admin_can_view_audit_log_page(): void
    {
        AuditLog::factory()->create();

        $admin = $this->getSeededAdmin();
        $this->actingAs($admin)
            ->get(AuditLogResource::getUrl('index'))
            ->assertOk();
    }

    public function test_non_super_admin_cannot_view_audit_log_page(): void
    {
        AuditLog::factory()->create();

        $redaksi = User::factory()->create();
        $redaksi->assignRole('admin_redaksi_berita');
        $this->actingAs($redaksi)
            ->get(AuditLogResource::getUrl('index'))
            ->assertForbidden();
    }

    private function getSeededAdmin(): User
    {
        return User::where('email', 'admin@disdikpora.karangasemkab.go.id')->firstOrFail();
    }
}
