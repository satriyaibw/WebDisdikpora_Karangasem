<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

/**
 * Fase 5: Seed data awal Katalog Layanan Publik (MasterPlan 5.1).
 *
 * Idempotent — aman dijalankan berulang kali (updateOrCreate berdasarkan slug).
 * Contoh layanan tanpa berkas PDF (template formulir opsional).
 */
class ServiceSeeder extends Seeder
{
    /**
     * @var array<int, array<string, mixed>>
     */
    public const SERVICES = [
        [
            'name' => 'Mutasi Siswa',
            'slug' => 'mutasi-siswa',
            'bidang' => 'pembinaan-pendidikan-smp',
            'short_description' => 'Layanan perpindahan peserta didik antar sekolah dalam maupun luar kabupaten.',
            'description' => 'Mutasi siswa adalah perpindahan peserta didik dari satu sekolah ke sekolah lain dengan alasan tertentu sesuai ketentuan yang berlaku.',
            'requirements' => '<ul><li>Surat permohonan mutasi dari orang tua/wali</li><li>Surat keterangan pindah dari sekolah asal</li><li>Fotokopi kartu keluarga</li><li>Fotokopi akta kelahiran</li></ul>',
            'procedure' => '<ol><li>Mengajukan permohonan ke sekolah asal</li><li>Sekolah asal menerbitkan surat keterangan pindah</li><li>Menyerahkan berkas ke sekolah tujuan</li><li>Dinas menerbitkan rekomendasi</li></ol>',
            'estimated_time' => '3 Hari Kerja',
            'cost' => 'Rp 0 / Gratis',
            'pic_name' => 'Kasi Kelembagaan & Peserta Didik',
            'pic_contact' => '(0363) 21034',
            'status' => 'published',
        ],
        [
            'name' => 'Legalisir Ijazah',
            'slug' => 'legalisir-ijazah',
            'bidang' => 'sekretariat',
            'short_description' => 'Pengesahan fotokopi ijazah dan dokumen akademik untuk keperluan resmi.',
            'description' => 'Legalisir ijazah adalah pengesahan keaslian fotokopi ijazah oleh pejabat berwenang pada Dinas Pendidikan.',
            'requirements' => '<ul><li>Fotokopi ijazah yang akan dilegalisir</li><li>Ijazah asli untuk diverifikasi</li></ul>',
            'procedure' => '<ol><li>Membawa berkas ke loket layanan</li><li>Verifikasi ijazah asli oleh petugas</li><li>Legalisir fotokopi ijazah</li></ol>',
            'estimated_time' => '1 Hari Kerja',
            'cost' => 'Rp 0 / Gratis',
            'pic_name' => 'Kasi Kurikulum & Penilaian',
            'pic_contact' => '(0363) 21034',
            'status' => 'published',
        ],
        [
            'name' => 'Rekomendasi Penelitian',
            'slug' => 'rekomendasi-penelitian',
            'bidang' => 'sekretariat',
            'short_description' => 'Surat rekomendasi pelaksanaan penelitian di lingkungan sekolah Kabupaten Karangasem.',
            'description' => 'Rekomendasi penelitian diberikan kepada mahasiswa/peneliti yang akan melaksanakan penelitian di sekolah-sekolah di lingkungan Disdikpora Karangasem.',
            'requirements' => '<ul><li>Surat permohonan dari instansi/perguruan tinggi</li><li>Proposal penelitian</li><li>Fotokopi KTP peneliti</li></ul>',
            'procedure' => '<ol><li>Mengajukan surat permohonan</li><li>Verifikasi kelengkapan berkas</li><li>Penerbitan surat rekomendasi</li></ol>',
            'estimated_time' => '5 Hari Kerja',
            'cost' => 'Rp 0 / Gratis',
            'pic_name' => 'Kepala Sekretariat',
            'pic_contact' => '(0363) 21034',
            'status' => 'published',
        ],
        [
            'name' => 'Operasional PAUD',
            'slug' => 'operasional-paud',
            'bidang' => 'pembinaan-pendidikan-paud-pnf',
            'short_description' => 'Bantuan operasional penyelenggaraan lembaga PAUD di Kabupaten Karangasem.',
            'description' => 'Layanan pendataan dan verifikasi lembaga PAUD penerima bantuan operasional penyelenggaraan (BOP PAUD).',
            'requirements' => '<ul><li>Profil lembaga PAUD</li><li>Nomor pokok pendidikan nasional</li><li>Rekening lembaga</li></ul>',
            'procedure' => '<ol><li>Pendataan lembaga melalui operator</li><li>Verifikasi berkas oleh Dinas</li><li>Penetapan penerima bantuan</li></ol>',
            'estimated_time' => '7 Hari Kerja',
            'cost' => 'Rp 0 / Gratis',
            'pic_name' => 'Kasi PAUD & PNF',
            'pic_contact' => '(0363) 21034',
            'status' => 'published',
        ],
        [
            'name' => 'Rekomendasi Kegiatan Kepemudaan',
            'slug' => 'rekomendasi-kegiatan-kepemudaan',
            'bidang' => 'pemuda-olahraga',
            'short_description' => 'Surat rekomendasi penyelenggaraan kegiatan kepemudaan di Kabupaten Karangasem.',
            'description' => 'Rekomendasi kegiatan kepemudaan diberikan kepada organisasi kepemudaan yang akan menyelenggarakan kegiatan di Kabupaten Karangasem.',
            'requirements' => '<ul><li>Surat permohonan organisasi</li><li>Proposal kegiatan</li><li>Susunan panitia</li></ul>',
            'procedure' => '<ol><li>Mengajukan permohonan resmi</li><li>Verifikasi proposal kegiatan</li><li>Penerbitan surat rekomendasi</li></ol>',
            'estimated_time' => '5 Hari Kerja',
            'cost' => 'Rp 0 / Gratis',
            'pic_name' => 'Kasi Kepemudaan',
            'pic_contact' => '(0363) 21034',
            'status' => 'published',
        ],
    ];

    public function run(): void
    {
        foreach (self::SERVICES as $service) {
            $bidang = Bidang::where('slug', $service['bidang'])->first();

            Service::updateOrCreate(
                ['slug' => $service['slug']],
                array_merge(
                    Arr::except($service, ['bidang']),
                    ['bidang_id' => $bidang?->id]
                )
            );
        }
    }
}
