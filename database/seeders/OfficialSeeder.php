<?php

namespace Database\Seeders;

use App\Models\Official;
use Illuminate\Database\Seeder;

/**
 * Seed pejabat awal untuk bagan struktur organisasi (MasterPlan 3.6).
 *
 * Idempotent — aman dijalankan berulang kali (firstOrCreate berdasarkan jabatan).
 */
class OfficialSeeder extends Seeder
{
    /**
     * @var array<int, array{jabatan: string, nama: string, parent?: string}>
     */
    public const OFFICIALS = [
        [
            'jabatan' => 'Kepala Dinas',
            'nama' => '-',
        ],
        [
            'jabatan' => 'Sekretariat',
            'nama' => '-',
            'parent' => 'Kepala Dinas',
        ],
        [
            'jabatan' => 'Kepala Bidang Pembinaan Pendidikan PAUD & PNF',
            'nama' => '-',
            'parent' => 'Sekretariat',
        ],
        [
            'jabatan' => 'Kepala Bidang Pembinaan Pendidikan SD',
            'nama' => '-',
            'parent' => 'Sekretariat',
        ],
        [
            'jabatan' => 'Kepala Bidang Pembinaan Pendidikan SMP',
            'nama' => '-',
            'parent' => 'Sekretariat',
        ],
        [
            'jabatan' => 'Kepala Bidang Pendidik & Tenaga Kependidikan',
            'nama' => '-',
            'parent' => 'Sekretariat',
        ],
        [
            'jabatan' => 'Kepala Bidang Kepemudaan',
            'nama' => '-',
            'parent' => 'Sekretariat',
        ],
        [
            'jabatan' => 'Kepala Bidang Olahraga',
            'nama' => '-',
            'parent' => 'Sekretariat',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $byJabatan = [];

        foreach (self::OFFICIALS as $index => $official) {
            $parentId = null;

            if (isset($official['parent']) && isset($byJabatan[$official['parent']])) {
                $parentId = $byJabatan[$official['parent']];
            }

            $record = Official::updateOrCreate(
                ['jabatan' => $official['jabatan']],
                [
                    'nama' => $official['nama'],
                    'parent_id' => $parentId,
                    'sort_order' => $index,
                    'is_active' => true,
                ]
            );

            $byJabatan[$official['jabatan']] = $record->id;
        }
    }
}
