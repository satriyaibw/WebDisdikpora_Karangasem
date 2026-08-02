<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\Album;
use App\Models\AlbumPhoto;
use App\Models\Announcement;
use App\Models\DownloadFile;
use App\Models\Infographic;
use App\Models\News;
use App\Models\Service;
use App\Models\Slider;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_struktur_page_renders_bidangs(): void
    {
        $this->get(route('profil.struktur'))
            ->assertOk()
            ->assertSee('Kepala Dinas')
            ->assertSee('Sekretariat');
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

        \Illuminate\Support\Facades\DB::table('settings')
            ->where('key', 'site.short_name')
            ->update(['value' => 'Nama Baru']);

        $this->assertSame('Disdikpora Karangasem', settings('site.short_name'));

        \App\Support\Settings::flush();

        $this->assertSame('Nama Baru', settings('site.short_name'));
    }

    public function test_escape_like_escapes_wildcards(): void
    {
        $this->assertSame('100\\%\\_x\\\\y', escapeLike('100%_x\\y'));
    }

    /** ============================= Regresi bug Fase 6 ============================= */
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
        $category = \App\Models\PpidCategory::create([
            'name' => 'Kategori Uji Badge',
            'slug' => 'kategori-uji-badge',
        ]);
        \App\Models\PpidDocument::create([
            'title' => 'Dokumen Terbit Uji',
            'file_path' => 'ppid/dokumen-terbit.pdf',
            'status' => \App\Models\PpidDocument::STATUS_PUBLISHED,
            'category_id' => $category->id,
        ]);
        \App\Models\PpidDocument::create([
            'title' => 'Dokumen Draft Uji',
            'file_path' => 'ppid/dokumen-draft.pdf',
            'status' => \App\Models\PpidDocument::STATUS_DRAFT,
            'category_id' => $category->id,
        ]);

        \Livewire\Livewire::test(\App\Livewire\Public\PpidTabs::class)
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
}
