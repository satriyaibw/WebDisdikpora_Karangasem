<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->actor = User::factory()->create();
        $this->actingAs($this->actor);
    }

    public function test_user_create_is_recorded_in_audit_logs(): void
    {
        $this->app['request']->server->set('REMOTE_ADDR', '203.0.113.10');
        $this->app['request']->server->set('HTTP_USER_AGENT', 'PHPUnit-Agent/11');
        $this->app['request']->headers->set('User-Agent', 'PHPUnit-Agent/11');

        $user = User::factory()->create(['name' => 'Budi Santoso']);

        $log = AuditLog::query()
            ->where('model_type', User::class)
            ->where('model_id', $user->id)
            ->where('action', 'create')
            ->first();

        $this->assertNotNull($log, 'Aksi create harus tercatat di audit_logs.');
        $this->assertEquals($this->actor->id, $log->user_id);
        $this->assertEquals('create', $log->action);
        $this->assertSame('Budi Santoso', $log->new_values['name']);
        $this->assertNull($log->old_values);
        $this->assertSame('203.0.113.10', $log->ip_address, 'IP address pelaku harus terekam.');
        $this->assertSame('PHPUnit-Agent/11', $log->user_agent, 'User-agent pelaku harus terekam.');
    }

    public function test_user_update_is_recorded_with_old_and_new_values(): void
    {
        $user = User::factory()->create(['name' => 'Budi Santoso']);
        $user->update(['name' => 'Budi Santoso Baru']);

        $log = AuditLog::query()
            ->where('model_type', User::class)
            ->where('model_id', $user->id)
            ->where('action', 'update')
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'Aksi update harus tercatat di audit_logs.');
        $this->assertEquals($this->actor->id, $log->user_id);
        $this->assertSame('Budi Santoso', $log->old_values['name']);
        $this->assertSame('Budi Santoso Baru', $log->new_values['name']);
    }

    public function test_user_delete_is_recorded(): void
    {
        $user = User::factory()->create(['name' => 'Budi Santoso']);
        $user->delete();

        $log = AuditLog::query()
            ->where('model_type', User::class)
            ->where('model_id', $user->id)
            ->where('action', 'delete')
            ->first();

        $this->assertNotNull($log, 'Aksi delete harus tercatat di audit_logs.');
        $this->assertEquals($this->actor->id, $log->user_id);
        $this->assertSame('Budi Santoso', $log->old_values['name']);
        $this->assertNull($log->new_values);
    }

    public function test_password_is_never_recorded_in_audit_logs(): void
    {
        $user = User::factory()->create();
        $user->update(['password' => 'Rahasia!2026']);

        $log = AuditLog::query()
            ->where('model_type', User::class)
            ->where('model_id', $user->id)
            ->where('action', 'update')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayNotHasKey('password', $log->new_values);
        $this->assertArrayNotHasKey('password', $log->old_values);
    }
}
