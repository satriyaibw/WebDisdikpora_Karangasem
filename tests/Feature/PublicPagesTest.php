<?php

namespace Tests\Feature;

use App\Livewire\Public\PpidTabs;
use App\Livewire\Public\ServiceCatalog;
use App\Livewire\Public\SopCatalog;
use App\Models\Agenda;
use App\Models\Album;
use App\Models\AlbumPhoto;
use App\Models\Announcement;
use App\Models\Bidang;
use App\Models\DownloadCategory;
use App\Models\DownloadFile;
use App\Models\Infographic;
use App\Models\News;
use App\Models\PpidCategory;
use App\Models\PpidDocument;
use App\Models\ProfileSection;
use App\Models\RelatedLink;
use App\Models\Service;
use App\Models\Slider;
use App\Models\SopDocument;
use App\Models\Video;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    private function makeSlider(array $attributes = []): Slider
    {
        return Slider::create(array_merge([
            'image' => 'sliders/hero.jpg',
            'title' => 'Selamat Datang di Disdikpora Karangasem',
            'description' => 'Portal resmi layanan publik',
            'sort_order' => 0,
            'is_active' => true,
        ], $attributes));
    }

    private function makeNews(array $attributes = []): News
    {
        return News::create(array_merge([
            'title' => 'Berita Uji Publik',
            'slug' => 'berita-uji-publik',
            'excerpt' => 'Ringkasan berita uji publik',
            'content' => '<p>Isi lengkap berita uji publik.</p>',
            'status' => News::STATUS_PUBLISHED,
            'published_at' => now(),
        ], $attributes));
    }

    private function makeAnnouncement(array $attributes = []): Announcement
    {
        return Announcement::create(array_merge([
            'title' => 'Pengumuman Uji Publik',
            'content' => '<p>Isi pengumuman uji publik.</p>',
            'is_important' => true,
            'status' => Announcement::STATUS_PUBLISHED,
            'announcement_date' => today(),
        ], $attributes));
    }

    /** ============================= Halaman Utama ============================= */
    public function test_homepage_shows_slider_news_agenda_and_announcement(): void
    {
        $slider = $this->makeSlider();
        $news = $this->makeNews();
        $announcement = $this->makeAnnouncement();
        $agenda = Agenda::create([
            'title' => 'Rapat Koordinasi Dinas',
            'date' => today()->addDays(2),
            'location' => 'Aula Disdikpora',
        ]);
        $infographic = Infographic::create(['title' => 'Data Guru 2026', 'image' => 'infografis/data-guru.jpg', 'is_active' => true]);
        $video = Video::create([
            'title' => 'Video Profil Dinas',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'status' => Video::STATUS_PUBLISHED,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($slider->title)
            ->assertSee($announcement->title)
            ->assertSee($news->title)
            ->assertSee($agenda->title)
            ->assertSee($infographic->title)
            ->assertSee($video->title);
    }

    public function test_homepage_does_not_show_welcome_page_or_inactive_content(): void
    {
        $this->makeSlider(['is_active' => false, 'title' => 'Slider Nonaktif']);
        $this->makeNews(['status' => News::STATUS_DRAFT, 'title' => 'Berita Draft Rahasia']);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Slider Nonaktif')
            ->assertDontSee('Berita Draft Rahasia')
            ->assertDontSee('Laravel');
    }

    public function test_homepage_running_text_falls_back_to_latest_published_announcement(): void
    {
        $this->makeAnnouncement(['is_important' => false, 'title' => 'Pengumuman Biasa']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Pengumuman Biasa');
    }

    /** ============================= Profil ============================= */
    public function test_profil_page_renders(): void
    {
        $this->get(route('profil'))
            ->assertOk()
            ->assertSee('Sambutan Kepala Dinas')
            ->assertSee('Visi')
            ->assertSee('Misi')
            ->assertSee(route('profil.struktur'));
    }

    /** ============================= Layanan ============================= */
    public function test_layanan_index_lists_seeded_services(): void
    {
        $this->get(route('layanan.index'))
            ->assertOk()
            ->assertSee('Katalog Layanan Publik')
            ->assertSee('Mutasi Siswa')
            ->assertSee('Legalisir Ijazah');
    }

    public function test_layanan_show_renders_all_details(): void
    {
        $this->get(route('layanan.show', 'mutasi-siswa'))
            ->assertOk()
            ->assertSee('Mutasi Siswa')
            ->assertSee('Deskripsi Layanan')
            ->assertSee('Persyaratan')
            ->assertSee('Bagan Alur Prosedur')
            ->assertSee('3 Hari Kerja')
            ->assertSee('Gratis');
    }

    public function test_draft_service_is_not_accessible_publicly(): void
    {
        Service::create([
            'name' => 'Layanan Rahasia',
            'slug' => 'layanan-rahasia',
            'status' => Service::STATUS_DRAFT,
        ]);

        $this->get(route('layanan.index'))->assertDontSee('Layanan Rahasia');
        $this->get(route('layanan.show', 'layanan-rahasia'))->assertNotFound();
    }

    /** ============================= SOP ============================= */
    public function test_sop_index_lists_seeded_documents(): void
    {
        $this->get(route('sop.index'))
            ->assertOk()
            ->assertSee('Dokumen SOP')
            ->assertSee('SOP Pelayanan Legalisir Ijazah');
    }

    public function test_sop_show_renders_pdf_preview_and_metadata(): void
    {
        $this->get(route('sop.show', 'sop-legalisir-ijazah'))
            ->assertOk()
            ->assertSee('Pratinjau Dokumen')
            ->assertSee('Unduh Berkas')
            ->assertSee('Detail Dokumen');
    }

    /** ============================= PPID ============================= */
    public function test_ppid_page_renders_tabs_and_documents(): void
    {
        $this->get(route('ppid.index'))
            ->assertOk()
            ->assertSee('Informasi PPID')
            ->assertSee('Informasi Berkala')
            ->assertSee('Informasi Serta Merta')
            ->assertSee('Informasi Setiap Saat')
            ->assertSee('LAKIP Disdikpora Karangasem');
    }

    /** ============================= Berita ============================= */
    public function test_berita_index_shows_only_published_news(): void
    {
        $this->makeNews(['title' => 'Berita Terbit Uji']);
        $this->makeNews(['slug' => 'berita-draft', 'title' => 'Berita Draft Rahasia', 'status' => News::STATUS_DRAFT]);

        $this->get(route('berita.index'))
            ->assertOk()
            ->assertSee('Berita Terbit Uji')
            ->assertDontSee('Berita Draft Rahasia');
    }

    public function test_berita_show_increments_views_and_renders_content(): void
    {
        $news = $this->makeNews(['title' => 'Berita Detail Uji', 'slug' => 'berita-detail-uji']);

        $this->get(route('berita.show', $news->slug))
            ->assertOk()
            ->assertSee('Berita Detail Uji')
            ->assertSee('Isi lengkap berita uji publik.');

        $this->assertEquals(1, $news->fresh()->views_count);
    }

    public function test_berita_views_counted_once_per_day(): void
    {
        $news = $this->makeNews(['title' => 'Berita Throttle Uji', 'slug' => 'berita-throttle-uji']);

        $this->get(route('berita.show', $news->slug));
        $this->get(route('berita.show', $news->slug));
        $this->get(route('berita.show', $news->slug));

        $this->assertEquals(1, $news->fresh()->views_count);
    }

    public function test_draft_news_detail_is_not_accessible(): void
    {
        $this->makeNews(['slug' => 'berita-draft', 'status' => News::STATUS_DRAFT]);

        $this->get(route('berita.show', 'berita-draft'))->assertNotFound();
    }

    /** ============================= Pengumuman ============================= */
    public function test_pengumuman_index_shows_only_published(): void
    {
        $this->makeAnnouncement(['title' => 'Pengumuman Resmi Uji']);
        $this->makeAnnouncement(['title' => 'Pengumuman Draft Rahasia', 'status' => Announcement::STATUS_DRAFT]);

        $this->get(route('pengumuman.index'))
            ->assertOk()
            ->assertSee('Pengumuman Resmi Uji')
            ->assertDontSee('Pengumuman Draft Rahasia');
    }

    /** ============================= Agenda ============================= */
    public function test_agenda_index_shows_upcoming_and_finished(): void
    {
        Agenda::create(['title' => 'Agenda Mendatang', 'date' => today()->addDays(3)]);
        Agenda::create(['title' => 'Agenda Terlaksana', 'date' => today()->subDays(3)]);

        $this->get(route('agenda.index'))
            ->assertOk()
            ->assertSee('Agenda Mendatang')
            ->assertSee('Agenda Terlaksana');
    }

    /** ============================= Galeri ============================= */
    public function test_galeri_index_shows_albums(): void
    {
        Album::create(['title' => 'Perayaan Hardiknas 2026']);

        $this->get(route('galeri.index'))
            ->assertOk()
            ->assertSee('Perayaan Hardiknas 2026');
    }

    public function test_galeri_show_shows_album_photos(): void
    {
        $album = Album::create(['title' => 'Album Kegiatan Uji']);
        AlbumPhoto::create(['album_id' => $album->id, 'photo_path' => 'galeri/foto-1.jpg', 'caption' => 'Dokumentasi kegiatan']);

        $this->get(route('galeri.show', $album))
            ->assertOk()
            ->assertSee('Album Kegiatan Uji')
            ->assertSee('Dokumentasi kegiatan');
    }

    /** ============================= Unduhan ============================= */
    public function test_unduhan_index_lists_seeded_files(): void
    {
        $this->get(route('unduhan.index'))
            ->assertOk()
            ->assertSee('Pusat Unduhan')
            ->assertSee('Formulir Pendaftaran Peserta Didik Baru')
            ->assertSee('Juknis Bantuan Operasional Sekolah (BOS)');
    }

    public function test_unduhan_hides_draft_files(): void
    {
        DownloadFile::create([
            'title' => 'Berkas Rahasia',
            'slug' => 'berkas-rahasia',
            'file_path' => 'lampiran/unduhan/rahasia.pdf',
            'status' => DownloadFile::STATUS_DRAFT,
        ]);

        $this->get(route('unduhan.index'))
            ->assertOk()
            ->assertDontSee('Berkas Rahasia');
    }

    public function test_unduhan_groups_files_by_category_and_hides_empty_categories(): void
    {
        $category = DownloadCategory::create([
            'name' => 'Kategori Uji Unduhan',
            'slug' => 'kategori-uji-unduhan',
            'sort_order' => 1,
        ]);
        $emptyCategory = DownloadCategory::create([
            'name' => 'Kategori Kosong',
            'slug' => 'kategori-kosong',
            'sort_order' => 2,
        ]);

        DownloadFile::create([
            'title' => 'Berkas Kategori Uji',
            'slug' => 'berkas-kategori-uji',
            'category_id' => $category->id,
            'file_path' => 'lampiran/unduhan/kategori-uji.pdf',
            'status' => DownloadFile::STATUS_PUBLISHED,
        ]);

        $this->get(route('unduhan.index'))
            ->assertOk()
            ->assertSee('Kategori Uji Unduhan')
            ->assertSee('Berkas Kategori Uji')
            ->assertDontSee('Kategori Kosong');
    }

    /** ============================= Kontak ============================= */
    public function test_kontak_page_renders_maps_and_lapor_link(): void
    {
        $this->get(route('kontak'))
            ->assertOk()
            ->assertSee('SP4N-LAPOR')
            ->assertSee('https://www.lapor.go.id')
            ->assertSee('google.com/maps')
            ->assertSee('Peta lokasi');
    }

    /** ============================= Helper settings ============================= */
    public function test_settings_helper_returns_seeded_values(): void
    {
        $this->assertSame('Disdikpora Karangasem', settings('site.short_name'));
        $this->assertSame('fallback', settings('key.tidak.ada', 'fallback'));
    }

    public function test_settings_flush_invalidates_cached_values(): void
    {
        settings('site.short_name');

        DB::table('settings')
            ->where('key', 'site.short_name')
            ->update(['value' => 'Nama Baru']);

        $this->assertSame('Disdikpora Karangasem', settings('site.short_name'));

        Settings::flush();

        $this->assertSame('Nama Baru', settings('site.short_name'));
    }

    public function test_escape_like_escapes_wildcards(): void
    {
        $this->assertSame('100\\%\\_x\\\\y', escapeLike('100%_x\\y'));
    }

    /** ============================= Regresi bug Fase 6 ============================= */
    public function test_ppid_search_does_not_trigger_error(): void
    {
        Livewire::test(PpidTabs::class)
            ->set('search', 'LAKIP')
            ->assertOk()
            ->assertSee('LAKIP Disdikpora Karangasem');
    }

    public function test_galeri_show_subtitle_includes_photo_count(): void
    {
        $album = Album::create(['title' => 'Album Dengan Foto']);
        AlbumPhoto::create(['album_id' => $album->id, 'photo_path' => 'galeri/foto-1.jpg']);
        AlbumPhoto::create(['album_id' => $album->id, 'photo_path' => 'galeri/foto-2.jpg']);

        $this->get(route('galeri.show', $album))
            ->assertOk()
            ->assertSee('Album foto — 2 foto');
    }

    public function test_galeri_index_shows_cover_photo_of_album(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('galeri/utama.jpg', 'gambar');

        $album = Album::create(['title' => 'Album Sampul']);
        AlbumPhoto::create(['album_id' => $album->id, 'photo_path' => 'galeri/sampul.jpg', 'sort_order' => 2]);
        AlbumPhoto::create(['album_id' => $album->id, 'photo_path' => 'galeri/utama.jpg', 'sort_order' => 1]);

        $this->get(route('galeri.index'))
            ->assertOk()
            ->assertSee('Album Sampul')
            ->assertSee('galeri/utama.jpg');
    }

    public function test_ppid_tab_badge_counts_only_published_documents(): void
    {
        $category = PpidCategory::create([
            'name' => 'Kategori Uji Badge',
            'slug' => 'kategori-uji-badge',
        ]);
        PpidDocument::create([
            'title' => 'Dokumen Terbit Uji',
            'file_path' => 'ppid/dokumen-terbit.pdf',
            'status' => PpidDocument::STATUS_PUBLISHED,
            'category_id' => $category->id,
        ]);
        PpidDocument::create([
            'title' => 'Dokumen Draft Uji',
            'file_path' => 'ppid/dokumen-draft.pdf',
            'status' => PpidDocument::STATUS_DRAFT,
            'category_id' => $category->id,
        ]);

        Livewire::test(PpidTabs::class)
            ->set('activeCategorySlug', 'kategori-uji-badge')
            ->assertSee('Dokumen Terbit Uji')
            ->assertDontSee('Dokumen Draft Uji');
    }

    public function test_published_news_with_future_published_at_is_not_accessible(): void
    {
        $this->makeNews([
            'title' => 'Berita Masa Depan',
            'slug' => 'berita-masa-depan',
            'published_at' => now()->addDay(),
        ]);

        $this->get(route('berita.index'))->assertDontSee('Berita Masa Depan');
        $this->get(route('berita.show', 'berita-masa-depan'))->assertNotFound();
    }

    public function test_service_with_paid_cost_shows_cost_instead_of_gratis(): void
    {
        Service::create([
            'name' => 'Layanan Berbayar Uji',
            'slug' => 'layanan-berbayar-uji',
            'cost' => 'Rp 50.000',
            'status' => Service::STATUS_PUBLISHED,
        ]);

        $this->get(route('layanan.show', 'layanan-berbayar-uji'))
            ->assertOk()
            ->assertSee('Rp 50.000')
            ->assertDontSee('Gratis');
    }

    public function test_news_content_is_sanitized_of_scripts(): void
    {
        $this->makeNews([
            'title' => 'Berita Sanitasi Uji',
            'slug' => 'berita-sanitasi-uji',
            'content' => '<p>Konten aman</p><script>alert("xss")</script>',
        ]);

        $this->get(route('berita.show', 'berita-sanitasi-uji'))
            ->assertOk()
            ->assertSee('Konten aman')
            ->assertDontSee('alert("xss")');
    }

    /** ============================= Profil & Struktur ============================= */
    public function test_profil_page_shows_seeded_profile_content(): void
    {
        $this->get(route('profil'))
            ->assertOk()
            ->assertSee('Sambutan Kepala Dinas')
            ->assertSee('Visi')
            ->assertSee('Terwujudnya sumber daya manusia yang unggul')
            ->assertSee('Misi')
            ->assertSee('Meningkatkan mutu dan pemerataan layanan pendidikan');
    }

    public function test_profil_page_shows_custom_kadis_name_from_settings(): void
    {
        Settings::set('profile.kadis_name', 'Drs. I Wayan Suparta, M.M.');

        $this->get(route('profil'))
            ->assertOk()
            ->assertSee('Drs. I Wayan Suparta, M.M.');
    }

    public function test_profil_page_shows_dynamic_section_in_sort_order(): void
    {
        ProfileSection::create([
            'title' => 'Program Prioritas',
            'slug' => 'program-prioritas',
            'content' => '<p>Prioritas pembangunan pendidikan 2026.</p>',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->get(route('profil'))->assertOk();

        $this->assertStringContainsString('Program Prioritas', $response->getContent());
        $this->assertStringContainsString('Prioritas pembangunan pendidikan 2026', $response->getContent());
    }

    public function test_profil_page_hides_inactive_section(): void
    {
        ProfileSection::create([
            'title' => 'Rahasia Internal',
            'slug' => 'rahasia-internal',
            'content' => '<p>Tidak boleh tampil.</p>',
            'sort_order' => 9,
            'is_active' => false,
        ]);

        $this->get(route('profil'))
            ->assertOk()
            ->assertDontSee('Rahasia Internal')
            ->assertDontSee('Tidak boleh tampil');
    }

    public function test_profil_page_sanitizes_section_content(): void
    {
        ProfileSection::create([
            'title' => 'Seksi Sanitasi',
            'slug' => 'seksi-sanitasi',
            'content' => '<p>Konten aman</p><script>alert("xss-seksi")</script>',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->get(route('profil'))
            ->assertOk()
            ->assertSee('Konten aman')
            ->assertDontSee('alert("xss-seksi")');
    }

    public function test_struktur_page_shows_uploaded_image(): void
    {
        Storage::fake('public');
        UploadedFile::fake()->image('struktur.png')->storeAs('struktur', 'struktur.png', 'public');
        Settings::set('profile.struktur_image', 'struktur/struktur.png');

        $this->get(route('profil.struktur'))
            ->assertOk()
            ->assertSee(Storage::disk('public')->url('struktur/struktur.png'));
    }

    public function test_struktur_page_shows_empty_state_when_no_image(): void
    {
        $this->get(route('profil.struktur'))
            ->assertOk()
            ->assertSee('Bagan struktur organisasi belum tersedia.');
    }

    public function test_footer_shows_seeded_related_link(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Tautan Terkait')
            ->assertSee('SP4N-LAPOR!')
            ->assertSee('https://www.lapor.go.id');
    }

    public function test_footer_hides_inactive_related_links(): void
    {
        RelatedLink::where('name', 'SP4N-LAPOR!')->update(['is_active' => false]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('SP4N-LAPOR!');
    }

    public function test_footer_lists_related_links_in_sort_order(): void
    {
        RelatedLink::create([
            'name' => 'Portal Kabupaten',
            'url' => 'https://www.karangasemkab.go.id',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        RelatedLink::create([
            'name' => 'JDIH Karangasem',
            'url' => 'https://jdih.karangasemkab.go.id',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $html = $this->get(route('home'))
            ->assertOk()
            ->getContent();

        $this->assertTrue(strpos($html, 'SP4N-LAPOR!') < strpos($html, 'Portal Kabupaten'));
        $this->assertTrue(strpos($html, 'Portal Kabupaten') < strpos($html, 'JDIH Karangasem'));
    }

    /** ============================= Katalog SOP ============================= */
    public function test_sop_catalog_paginates_at_ten_per_page(): void
    {
        foreach (range(1, 11) as $i) {
            SopDocument::create([
                'title' => "SOP Uji Paginasi {$i}",
                'slug' => "sop-uji-paginasi-{$i}",
                'sop_number' => "SOP/{$i}",
                'issuance_date' => today(),
                'file_path' => "sop/uji-{$i}.pdf",
                'status' => SopDocument::STATUS_PUBLISHED,
            ]);
        }

        $this->get(route('sop.index'))
            ->assertOk()
            ->assertSee('SOP Uji Paginasi 1')
            ->assertDontSee('SOP Uji Paginasi 6');

        $this->get(route('sop.index').'?page=2')
            ->assertOk()
            ->assertSee('SOP Uji Paginasi 6')
            ->assertDontSee('SOP Uji Paginasi 1');
    }

    public function test_sop_catalog_filters_by_bidang(): void
    {
        $bidangA = Bidang::create(['name' => 'Bidang Uji A', 'slug' => 'bidang-uji-a']);
        $bidangB = Bidang::create(['name' => 'Bidang Uji B', 'slug' => 'bidang-uji-b']);

        SopDocument::create([
            'title' => 'SOP Bidang A',
            'slug' => 'sop-bidang-a',
            'sop_number' => 'SOP/A',
            'issuance_date' => today(),
            'file_path' => 'sop/a.pdf',
            'bidang_id' => $bidangA->id,
            'status' => SopDocument::STATUS_PUBLISHED,
        ]);
        SopDocument::create([
            'title' => 'SOP Bidang B',
            'slug' => 'sop-bidang-b',
            'sop_number' => 'SOP/B',
            'issuance_date' => today(),
            'file_path' => 'sop/b.pdf',
            'bidang_id' => $bidangB->id,
            'status' => SopDocument::STATUS_PUBLISHED,
        ]);

        Livewire::test(SopCatalog::class)
            ->call('setBidang', $bidangA->id)
            ->assertOk()
            ->assertSee('SOP Bidang A')
            ->assertDontSee('SOP Bidang B');
    }

    public function test_sop_catalog_searches_by_title_or_sop_number(): void
    {
        SopDocument::create([
            'title' => 'SOP Pelayanan Perizinan',
            'slug' => 'sop-perizinan',
            'sop_number' => 'SOP/2025/042',
            'issuance_date' => today(),
            'file_path' => 'sop/perizinan.pdf',
            'status' => SopDocument::STATUS_PUBLISHED,
        ]);
        SopDocument::create([
            'title' => 'SOP Rekrutmen',
            'slug' => 'sop-rekrutmen',
            'sop_number' => 'SOP/2025/010',
            'issuance_date' => today(),
            'file_path' => 'sop/rekrutmen.pdf',
            'status' => SopDocument::STATUS_PUBLISHED,
        ]);

        Livewire::test(SopCatalog::class)
            ->set('search', 'perizinan')
            ->assertOk()
            ->assertSee('SOP Pelayanan Perizinan')
            ->assertDontSee('SOP Rekrutmen');

        Livewire::test(SopCatalog::class)
            ->set('search', 'SOP/2025/010')
            ->assertOk()
            ->assertSee('SOP Rekrutmen')
            ->assertDontSee('SOP Pelayanan Perizinan');
    }

    /** ============================= Katalog Layanan ============================= */
    public function test_service_catalog_filters_by_bidang(): void
    {
        $bidangA = Bidang::create(['name' => 'Bidang Layanan A', 'slug' => 'bidang-layanan-a']);
        $bidangB = Bidang::create(['name' => 'Bidang Layanan B', 'slug' => 'bidang-layanan-b']);

        Service::create([
            'name' => 'Layanan Bidang A',
            'slug' => 'layanan-bidang-a',
            'bidang_id' => $bidangA->id,
            'status' => Service::STATUS_PUBLISHED,
        ]);
        Service::create([
            'name' => 'Layanan Bidang B',
            'slug' => 'layanan-bidang-b',
            'bidang_id' => $bidangB->id,
            'status' => Service::STATUS_PUBLISHED,
        ]);

        Livewire::test(ServiceCatalog::class)
            ->call('setBidang', $bidangA->id)
            ->assertOk()
            ->assertSee('Layanan Bidang A')
            ->assertDontSee('Layanan Bidang B');
    }

    public function test_service_catalog_searches_by_keyword(): void
    {
        Service::create([
            'name' => 'Surat Keterangan Aktif',
            'slug' => 'sk-aktif-khusus-uji',
            'short_description' => 'Keterangan aktif belajar',
            'status' => Service::STATUS_PUBLISHED,
        ]);
        Service::create([
            'name' => 'Legalisir Ijazah Khusus Uji',
            'slug' => 'legalisir-ijazah-khusus-uji',
            'short_description' => 'Pengesahan salinan ijazah',
            'status' => Service::STATUS_PUBLISHED,
        ]);

        Livewire::test(ServiceCatalog::class)
            ->set('search', 'keterangan')
            ->assertOk()
            ->assertSee('Surat Keterangan Aktif')
            ->assertDontSee('Legalisir Ijazah Khusus Uji');
    }
}
