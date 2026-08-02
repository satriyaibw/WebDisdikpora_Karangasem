<?php

namespace Database\Seeders;

use App\Models\DownloadCategory;
use Illuminate\Database\Seeder;

/**
 * Seed kategori awal Pusat Unduhan Berkas (MasterPlan 5.3).
 *
 * Idempotent — aman dijalankan berulang kali (updateOrCreate berdasarkan
 * slug, sehingga admin tetap bisa mengubah label nama kategori).
 */
class DownloadCategorySeeder extends Seeder
{
    /**
     * @var array<int, array{slug: string, name: string}>
     */
    public const CATEGORIES = [
        ['slug' => 'formulir', 'name' => 'Formulir'],
        ['slug' => 'juknis', 'name' => 'Petunjuk Teknis (Juknis)'],
        ['slug' => 'lainnya', 'name' => 'Lainnya'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::CATEGORIES as $index => $category) {
            DownloadCategory::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'sort_order' => $index,
                ]
            );
        }
    }
}
