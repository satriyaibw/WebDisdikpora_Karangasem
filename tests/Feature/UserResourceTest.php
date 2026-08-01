<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_creating_user_via_form_syncs_roles_by_id(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'User Baru',
                'email' => 'baru@example.com',
                'password' => 'secret123',
                'is_active' => true,
                'roles' => [Role::where('name', 'admin_redaksi_berita')->first()->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'baru@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('admin_redaksi_berita'));

        $pivot = $user->roles()->first()->pivot;
        $this->assertEquals(Role::where('name', 'admin_redaksi_berita')->first()->id, $pivot->role_id);
    }

    public function test_super_admin_cannot_delete_self_or_last_super_admin(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $this->assertFalse(UserResource::canDelete($admin));
    }

    public function test_super_admin_can_delete_another_active_super_admin(): void
    {
        $admin = $this->getSeededAdmin();
        $second = User::factory()->create(['is_active' => true]);
        $second->assignRole('super_admin');
        $this->actingAs($admin);

        $this->assertTrue(UserResource::canDelete($second));
    }

    public function test_last_active_super_admin_account_is_protected(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $this->assertTrue(UserResource::isProtectedAccount($admin));
    }

    public function test_inactive_super_admin_account_is_not_protected(): void
    {
        $admin = $this->getSeededAdmin();
        $inactive = User::factory()->create(['is_active' => false]);
        $inactive->assignRole('super_admin');
        $this->actingAs($admin);

        $this->assertFalse(UserResource::isProtectedAccount($inactive));
    }

    public function test_non_super_admin_does_not_see_super_admin_users(): void
    {
        $redaksi = User::factory()->create();
        $redaksi->assignRole('admin_redaksi_berita');
        $redaksi->givePermissionTo('user.read');
        $this->actingAs($redaksi);

        $emails = UserResource::getEloquentQuery()->pluck('email')->all();

        $this->assertNotContains('admin@disdikpora.karangasemkab.go.id', $emails);
    }

    public function test_super_admin_sees_all_users(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $emails = UserResource::getEloquentQuery()->pluck('email')->all();

        $this->assertContains('admin@disdikpora.karangasemkab.go.id', $emails);
    }

    public function test_role_management_follows_dot_permission_convention(): void
    {
        $manager = Role::firstOrCreate(['name' => 'role_manager', 'guard_name' => 'web']);
        $manager->syncPermissions(['role.read', 'role.update', 'permission.read']);

        $editor = User::factory()->create();
        $editor->assignRole('role_manager');

        $role = Role::where('name', 'admin_redaksi_berita')->firstOrFail();
        $permission = Permission::where('name', 'user.read')->firstOrFail();

        $this->assertTrue($editor->can('viewAny', Role::class));
        $this->assertTrue($editor->can('update', $role));
        $this->assertTrue($editor->can('viewAny', Permission::class));
        $this->assertFalse($editor->can('delete', $role));
        $this->assertFalse($editor->can('forceDelete', $role));

        $noop = User::factory()->create();
        $this->assertFalse($noop->can('viewAny', Role::class));
        $this->assertFalse($noop->can('viewAny', Permission::class));
    }

    public function test_role_permission_seeder_grants_panel_access_permission(): void
    {
        $this->seed(RolePermissionSeeder::class);

        foreach (['admin_redaksi_berita', 'admin_ppid_sop', 'admin_layanan_publik'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            $this->assertTrue($role->hasPermissionTo('panel.access'));
        }
    }

    private function getSeededAdmin(): User
    {
        return User::where('email', 'admin@disdikpora.karangasemkab.go.id')->firstOrFail();
    }
}
