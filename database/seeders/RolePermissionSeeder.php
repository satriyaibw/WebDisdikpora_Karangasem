<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Daftar permission baseline Fase 2 dengan konvensi `{modul}.{aksi}`.
     * Modul konten Fase 3+ (berita, ppid, layanan, dll) ditambahkan
     * pada seeder di fase masing-masing.
     */
    public const PERMISSIONS = [
        'panel.access',
        'user.read',
        'user.create',
        'user.update',
        'user.delete',
        'role.read',
        'role.create',
        'role.update',
        'role.delete',
        'permission.read',
        'permission.create',
        'permission.update',
        'permission.delete',
        'audit.read',
    ];

    /**
     * Mapping label Bahasa Indonesia untuk setiap role.
     */
    public const ROLE_LABELS = [
        'super_admin' => 'Super Admin',
        'admin_redaksi_berita' => 'Admin Redaksi / Berita',
        'admin_ppid_sop' => 'Admin PPID & SOP',
        'admin_layanan_publik' => 'Admin Layanan Publik',
    ];

    /**
     * Seed role & permission secara idempotent.
     *
     * Aman dijalankan berulang kali (firstOrCreate + syncPermissions).
     */
    public function run(): void
    {
        $this->seedPermissions();

        $roles = [
            'super_admin' => self::PERMISSIONS,
            'admin_redaksi_berita' => ['panel.access'],
            'admin_ppid_sop' => ['panel.access'],
            'admin_layanan_publik' => ['panel.access'],
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web']
            );

            $role->syncPermissions($permissions);
        }

        $this->assignSuperAdminToAdminUser();
        $this->flushPermissionCache();
    }

    /**
     * Buat permission yang belum ada (tidak menghapus permission lama).
     */
    private function seedPermissions(): void
    {
        foreach (self::PERMISSIONS as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web']
            );
        }
    }

    /**
     * Pastikan user admin seeder memiliki peran Super Admin.
     */
    private function assignSuperAdminToAdminUser(): void
    {
        $admin = User::where('email', 'admin@disdikpora.karangasemkab.go.id')->first();

        if ($admin !== null) {
            $admin->syncRoles(['super_admin']);
        }
    }

    /**
     * Bersihkan cache permission agar hasil seeder langsung terbaca.
     */
    private function flushPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
