<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Kategori default berita (MasterPlan 3.1).
     * Dapat diperluas lewat panel admin.
     */
    public const CATEGORIES = [
        ['name' => 'Pendidikan', 'slug' => 'pendidikan', 'description' => 'Berita seputar dunia pendidikan'],
        ['name' => 'Kepemudaan', 'slug' => 'kepemudaan', 'description' => 'Berita seputar kepemudaan'],
        ['name' => 'Olahraga', 'slug' => 'olahraga', 'description' => 'Berita seputar olahraga'],
        ['name' => 'Umum', 'slug' => 'umum', 'description' => 'Berita umum lainnya'],
    ];

    /**
     * Seed kategori default secara idempotent.
     */
    public function run(): void
    {
        foreach (self::CATEGORIES as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
