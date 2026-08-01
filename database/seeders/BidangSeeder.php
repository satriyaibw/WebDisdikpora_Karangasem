<?php

namespace Database\Seeders;

use App\Models\Bidang;
use Illuminate\Database\Seeder;

/**
 * Fase 5: Seed master Bidang/Sub-Bagian (MasterPlan 5.2).
 *
 * Idempotent — aman dijalankan berulang kali (firstOrCreate berdasarkan slug).
 */
class BidangSeeder extends Seeder
{
    /**
     * @var array<int, array{name: string, slug: string, description: string}>
     */
    public const BIDANGS = [
        [
            'name' => 'Sekretariat',
            'slug' => 'sekretariat',
            'description' => 'Sekretariat Dinas Pendidikan, Kepemudaan dan Olahraga Kabupaten Karangasem.',
        ],
        [
            'name' => 'Pembinaan Pendidikan PAUD & PNF',
            'slug' => 'pembinaan-pendidikan-paud-pnf',
            'description' => 'Bidang Pembinaan Pendidikan Anak Usia Dini dan Pendidikan Nonformal.',
        ],
        [
            'name' => 'Pembinaan Pendidikan SD',
            'slug' => 'pembinaan-pendidikan-sd',
            'description' => 'Bidang Pembinaan Sekolah Dasar.',
        ],
        [
            'name' => 'Pembinaan Pendidikan SMP',
            'slug' => 'pembinaan-pendidikan-smp',
            'description' => 'Bidang Pembinaan Sekolah Menengah Pertama.',
        ],
        [
            'name' => 'Pendidik & Tenaga Kependidikan',
            'slug' => 'pendidik-tenaga-kependidikan',
            'description' => 'Bidang pembinaan pendidik dan tenaga kependidikan.',
        ],
        [
            'name' => 'Pemuda & Olahraga',
            'slug' => 'pemuda-olahraga',
            'description' => 'Bidang pembinaan kepemudaan dan keolahragaan.',
        ],
    ];

    public function run(): void
    {
        foreach (self::BIDANGS as $bidang) {
            Bidang::updateOrCreate(
                ['slug' => $bidang['slug']],
                $bidang
            );
        }
    }
}
