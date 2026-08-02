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
        $this->call(BidangSeeder::class);
        $this->call(ServiceSeeder::class);
        $this->call(SopSeeder::class);
        $this->call(DownloadCategorySeeder::class);
        $this->call(DownloadFileSeeder::class);
        $this->call(OfficialSeeder::class);
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
            ['key' => 'profile.kadis_name', 'value' => null, 'group' => 'profile'],
            ['key' => 'profile.sekretariat_name', 'value' => null, 'group' => 'profile'],
            ['key' => 'profile.welcome', 'value' => 'Assalamualaikum warahmatullahi wabarakatuh, salam sejahtera bagi kita semua.<br><br>Selamat datang di portal resmi Dinas Pendidikan, Kepemudaan dan Olahraga Kabupaten Karangasem. Portal ini kami hadirkan sebagai wujud komitmen transparansi informasi publik serta upaya peningkatan kualitas layanan pendidikan, kepemudaan, dan olahraga di Kabupaten Karangasem. Melalui portal ini, masyarakat dapat mengakses berbagai layanan, berita, pengumuman, serta dokumen informasi publik dengan mudah dan cepat.<br><br>Kami berharap kehadiran portal ini dapat mempermudah akses informasi bagi seluruh masyarakat. Saran dan masukan yang membangun sangat kami harapkan demi perbaikan pelayanan kami ke depan.', 'group' => 'profile'],
            ['key' => 'profile.vision', 'value' => 'Terwujudnya sumber daya manusia yang unggul, berkarakter, berdaya saing, dan berbudaya menuju Karangasem yang aman, sejahtera, dan bahagia.', 'group' => 'profile'],
            ['key' => 'profile.mission', 'value' => '<ol><li>Meningkatkan mutu dan pemerataan layanan pendidikan anak usia dini, dasar, dan menengah.</li><li>Meningkatkan pembinaan dan pengembangan kepemudaan serta prestasi olahraga.</li><li>Meningkatkan kapasitas dan profesionalisme tenaga pendidik dan kependidikan.</li><li>Mewujudkan tata kelola pemerintahan dinas yang bersih, transparan, dan akuntabel.</li><li>Meningkatkan partisipasi masyarakat dan dunia usaha dalam penyelenggaraan pendidikan.</li></ol>', 'group' => 'profile'],
            ['key' => 'profile.duties', 'value' => 'Melaksanakan urusan pemerintahan daerah di bidang pendidikan, kepemudaan, dan olahraga berdasarkan asas otonomi dan tugas pembantuan.', 'group' => 'profile'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
