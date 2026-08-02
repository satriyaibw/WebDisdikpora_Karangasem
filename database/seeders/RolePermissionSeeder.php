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
     * Daftar permission baseline dengan konvensi `{modul}.{aksi}`.
     * Fase 2: manajemen user/role/permission & audit log.
     * Fase 3: modul konten (berita, pengumuman, agenda, slider, galeri).
     * Fase 4: modul repositori dokumen PPID.
     * Fase 5: katalog layanan publik, repositori SOP & pusat unduhan.
     * Fase 6: seksi profil dinamis & tautan terkait footer.
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
        'kategori.read',
        'kategori.create',
        'kategori.update',
        'kategori.delete',
        'berita.read',
        'berita.create',
        'berita.update',
        'berita.delete',
        'pengumuman.read',
        'pengumuman.create',
        'pengumuman.update',
        'pengumuman.delete',
        'infografis.read',
        'infografis.create',
        'infografis.update',
        'infografis.delete',
        'agenda.read',
        'agenda.create',
        'agenda.update',
        'agenda.delete',
        'slider.read',
        'slider.create',
        'slider.update',
        'slider.delete',
        'galeri.read',
        'galeri.create',
        'galeri.update',
        'galeri.delete',
        'video.read',
        'video.create',
        'video.update',
        'video.delete',
        'ppid.read',
        'ppid.create',
        'ppid.update',
        'ppid.delete',
        'layanan.read',
        'layanan.create',
        'layanan.update',
        'layanan.delete',
        'sop.read',
        'sop.create',
        'sop.update',
        'sop.delete',
        'unduhan.read',
        'unduhan.create',
        'unduhan.update',
        'unduhan.delete',
        'setting.read',
        'setting.update',
        'profile.read',
        'profile.create',
        'profile.update',
        'profile.delete',
        'tautan.read',
        'tautan.create',
        'tautan.update',
        'tautan.delete',
    ];

    /**
     * Permission modul konten Fase 3 yang dimiliki Admin Redaksi / Berita.
     */
    public const CONTENT_PERMISSIONS = [
        'kategori.read',
        'kategori.create',
        'kategori.update',
        'kategori.delete',
        'berita.read',
        'berita.create',
        'berita.update',
        'berita.delete',
        'pengumuman.read',
        'pengumuman.create',
        'pengumuman.update',
        'pengumuman.delete',
        'infografis.read',
        'infografis.create',
        'infografis.update',
        'infografis.delete',
        'agenda.read',
        'agenda.create',
        'agenda.update',
        'agenda.delete',
        'slider.read',
        'slider.create',
        'slider.update',
        'slider.delete',
        'galeri.read',
        'galeri.create',
        'galeri.update',
        'galeri.delete',
        'video.read',
        'video.create',
        'video.update',
        'video.delete',
    ];

    /**
     * Permission modul PPID (Fase 4) yang dimiliki Admin PPID & SOP.
     */
    public const PPID_PERMISSIONS = [
        'ppid.read',
        'ppid.create',
        'ppid.update',
        'ppid.delete',
    ];

    /**
     * Permission modul Katalog Layanan Publik (Fase 5) yang dimiliki Admin Layanan Publik.
     */
    public const LAYANAN_PERMISSIONS = [
        'layanan.read',
        'layanan.create',
        'layanan.update',
        'layanan.delete',
    ];

    /**
     * Permission modul Repositori Dokumen SOP (Fase 5) yang dimiliki Admin PPID & SOP.
     */
    public const SOP_PERMISSIONS = [
        'sop.read',
        'sop.create',
        'sop.update',
        'sop.delete',
    ];

    /**
     * Permission modul Pusat Unduhan Berkas (Fase 5) yang dimiliki Admin Layanan Publik.
     */
    public const UNDUHAN_PERMISSIONS = [
        'unduhan.read',
        'unduhan.create',
        'unduhan.update',
        'unduhan.delete',
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
            'admin_redaksi_berita' => array_merge(['panel.access'], self::CONTENT_PERMISSIONS),
            'admin_ppid_sop' => array_merge(['panel.access'], self::PPID_PERMISSIONS, self::SOP_PERMISSIONS),
            'admin_layanan_publik' => array_merge(['panel.access'], self::LAYANAN_PERMISSIONS, self::UNDUHAN_PERMISSIONS),
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
     * Menggunakan assignRole (aditif) agar peran lain yang
     * di-assign manual lewat panel tidak ikut terhapus.
     */
    private function assignSuperAdminToAdminUser(): void
    {
        $admin = User::where('email', 'admin@disdikpora.karangasemkab.go.id')->first();

        if ($admin !== null && ! $admin->hasRole('super_admin')) {
            $admin->assignRole('super_admin');
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
