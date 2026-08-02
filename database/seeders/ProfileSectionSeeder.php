<?php

namespace Database\Seeders;

use App\Models\ProfileSection;
use Illuminate\Database\Seeder;

/**
 * Seed seksi awal halaman Profil (MasterPlan 3.6).
 *
 * Idempotent — aman dijalankan berulang kali (updateOrCreate berdasarkan
 * slug, sehingga admin tetap bisa mengubah judul/isi seksi).
 */
class ProfileSectionSeeder extends Seeder
{
    /**
     * @var array<int, array{slug: string, title: string, content: string}>
     */
    public const SECTIONS = [
        [
            'slug' => 'visi',
            'title' => 'Visi',
            'content' => 'Terwujudnya sumber daya manusia yang unggul, berkarakter, berdaya saing, dan berbudaya menuju Karangasem yang aman, sejahtera, dan bahagia.',
        ],
        [
            'slug' => 'misi',
            'title' => 'Misi',
            'content' => '<ol><li>Meningkatkan mutu dan pemerataan layanan pendidikan anak usia dini, dasar, dan menengah.</li><li>Meningkatkan pembinaan dan pengembangan kepemudaan serta prestasi olahraga.</li><li>Meningkatkan kapasitas dan profesionalisme tenaga pendidik dan kependidikan.</li><li>Mewujudkan tata kelola pemerintahan dinas yang bersih, transparan, dan akuntabel.</li><li>Meningkatkan partisipasi masyarakat dan dunia usaha dalam penyelenggaraan pendidikan.</li></ol>',
        ],
        [
            'slug' => 'tugas-fungsi',
            'title' => 'Tugas & Fungsi',
            'content' => 'Melaksanakan urusan pemerintahan daerah di bidang pendidikan, kepemudaan, dan olahraga berdasarkan asas otonomi dan tugas pembantuan.',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::SECTIONS as $index => $section) {
            ProfileSection::updateOrCreate(
                ['slug' => $section['slug']],
                [
                    'title' => $section['title'],
                    'content' => $section['content'],
                    'sort_order' => $index,
                    'is_active' => true,
                ]
            );
        }
    }
}
