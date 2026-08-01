<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_four_default_roles(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $roles = Role::query()->orderBy('id')->pluck('name')->all();

        $this->assertEquals([
            'super_admin',
            'admin_redaksi_berita',
            'admin_ppid_sop',
            'admin_layanan_publik',
        ], $roles);
    }

    public function test_seeder_creates_baseline_permissions_with_dot_convention(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $permissions = Permission::query()->orderBy('id')->pluck('name')->all();

        foreach (RolePermissionSeeder::PERMISSIONS as $permission) {
            $this->assertContains($permission, $permissions, "Permission {$permission} harus tersedia.");
        }

        foreach ($permissions as $permission) {
            $this->assertMatchesRegularExpression('/^[a-z]+\.[a-z_]+$/', $permission, "Permission {$permission} harus mengikuti konvensi {modul}.{aksi}.");
        }
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $this->assertEquals(4, Role::count());
        $this->assertEquals(count(RolePermissionSeeder::PERMISSIONS), Permission::count());
    }

    public function test_super_admin_role_has_all_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $superAdmin = Role::where('name', 'super_admin')->first();

        $this->assertEqualsCanonicalizing(
            RolePermissionSeeder::PERMISSIONS,
            $superAdmin->permissions->pluck('name')->all()
        );
    }

    public function test_redaksi_role_gets_panel_access_and_all_content_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $role = Role::where('name', 'admin_redaksi_berita')->first();

        $this->assertEqualsCanonicalizing(
            array_merge(['panel.access'], RolePermissionSeeder::CONTENT_PERMISSIONS),
            $role->permissions->pluck('name')->all()
        );
    }

    public function test_ppid_and_layanan_roles_only_get_panel_access(): void
    {
        $this->seed(RolePermissionSeeder::class);

        foreach (['admin_ppid_sop', 'admin_layanan_publik'] as $roleName) {
            $role = Role::where('name', $roleName)->first();

            $this->assertEqualsCanonicalizing(
                ['panel.access'],
                $role->permissions->pluck('name')->all(),
                "Role {$roleName} hanya boleh memiliki panel.access."
            );
        }
    }

    public function test_admin_seeder_user_gets_super_admin_role(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@disdikpora.karangasemkab.go.id')->first();

        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole('super_admin'));
    }

    public function test_re_seeding_keeps_manually_assigned_roles_on_admin_user(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@disdikpora.karangasemkab.go.id')->firstOrFail();
        $admin->assignRole('admin_ppid_sop');

        $this->seed(RolePermissionSeeder::class);

        $admin->refresh();

        $this->assertTrue($admin->hasRole('super_admin'));
        $this->assertTrue($admin->hasRole('admin_ppid_sop'));
    }
}
