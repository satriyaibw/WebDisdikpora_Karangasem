<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedAdminUser();
        $this->seedSettings();
        $this->call(RolePermissionSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(PpidSeeder::class);
    }

    private function seedAdminUser(): void
    {
        $admin = User::query()->firstOrNew(['email' => 'admin@disdikpora.karangasemkab.go.id']);
        $admin->name = 'Administrator Disdikpora';

        $explicitPassword = config('app.admin_initial_password');

        if (! $admin->exists || $explicitPassword) {
            $password = $explicitPassword ?? Str::password();
            $admin->password = $password;

            if (! $explicitPassword) {
                $this->command->warn("ADMIN_INITIAL_PASSWORD tidak diset di .env - password admin acak: {$password}");
            }
        }

        $admin->save();
    }

    private function seedSettings(): void
    {
        $settings = [
            ['key' => 'site.name', 'value' => 'Dinas Pendidikan, Kepemudaan dan Olahraga Kabupaten Karangasem', 'group' => 'general'],
            ['key' => 'site.short_name', 'value' => 'Disdikpora Karangasem', 'group' => 'general'],
            ['key' => 'site.tagline', 'value' => 'Melayani dengan Hati', 'group' => 'general'],
            ['key' => 'site.address', 'value' => 'Jl. Ngurah Rai, Amlapura, Kabupaten Karangasem, Bali', 'group' => 'contact'],
            ['key' => 'site.email', 'value' => 'info@disdikpora.karangasemkab.go.id', 'group' => 'contact'],
            ['key' => 'site.phone', 'value' => '(0363) 21034', 'group' => 'contact'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
