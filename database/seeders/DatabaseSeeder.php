<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedAdminUser();
        $this->seedSettings();
    }

    private function seedAdminUser(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@disdikpora.karangasemkab.go.id'],
            [
                'name' => 'Administrator Disdikpora',
                'password' => Hash::make(env('ADMIN_INITIAL_PASSWORD', 'Password!2026')),
            ]
        );
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
