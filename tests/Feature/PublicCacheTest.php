<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\AlbumPhoto;
use App\Models\News;
use App\Support\PublicCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PublicCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    private function taggedHas(array $tags, string $key): bool
    {
        return Cache::tags($tags)->has('public.'.$key);
    }

    public function test_homepage_queries_are_stored_in_public_cache(): void
    {
        $this->get(route('home'))->assertOk();

        $this->assertTrue($this->taggedHas([PublicCache::TAG_HOME], PublicCache::HOME_SLIDERS));
        $this->assertTrue($this->taggedHas([PublicCache::TAG_HOME, PublicCache::TAG_NEWS], PublicCache::HOME_LATEST_NEWS));
        $this->assertTrue($this->taggedHas([PublicCache::TAG_HOME, PublicCache::TAG_ANNOUNCEMENTS], PublicCache::HOME_RUNNING_TEXTS));
        $this->assertTrue($this->taggedHas([PublicCache::TAG_HOME, PublicCache::TAG_AGENDA], PublicCache::HOME_UPCOMING_AGENDAS));
    }

    public function test_news_update_invalidates_home_cache_immediately(): void
    {
        $news = News::create([
            'title' => 'Judul Lama',
            'slug' => 'judul-lama',
            'excerpt' => 'Ringkasan',
            'content' => '<p>Isi</p>',
            'status' => News::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Judul Lama');

        $news->update(['title' => 'Judul Baru']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Judul Baru')
            ->assertDontSee('Judul Lama');
    }

    public function test_category_change_invalidates_news_list_and_home(): void
    {
        $news = News::create([
            'title' => 'Berita Kategori',
            'slug' => 'berita-kategori',
            'excerpt' => 'Ringkasan',
            'content' => '<p>Isi</p>',
            'status' => News::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->get(route('home'))->assertOk();
        $this->get('/berita')->assertOk();

        $news->delete();

        $this->get('/berita')->assertOk();
        $this->get(route('home'))->assertOk();
        $this->assertTrue($this->taggedHas([PublicCache::TAG_NEWS], PublicCache::NEWS_CATEGORIES));
    }

    public function test_album_photo_moved_invalidates_both_album_photo_caches(): void
    {
        $albumA = Album::create(['title' => 'Album A']);
        $albumB = Album::create(['title' => 'Album B']);

        $photo = AlbumPhoto::create([
            'album_id' => $albumA->id,
            'photo_path' => 'galeri/uji/lama.webp',
        ]);

        $this->get(route('galeri.show', $albumA))->assertOk();

        $this->assertTrue($this->taggedHas([PublicCache::TAG_GALERI], 'galeri.album.photos.'.$albumA->id));

        $photo->update(['album_id' => $albumB->id]);

        $this->assertFalse($this->taggedHas([PublicCache::TAG_GALERI], 'galeri.album.photos.'.$albumA->id));

        $this->get(route('galeri.show', $albumB))->assertOk();
    }

    public function test_homepage_builds_open_graph_meta(): void
    {
        $response = $this->get(route('home'))->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('property="og:site_name"', $content);
        $this->assertStringContainsString('property="og:type" content="website"', $content);
        $this->assertStringContainsString('property="og:title"', $content);
        $this->assertStringContainsString('property="og:image"', $content);
        $this->assertStringContainsString('name="twitter:card" content="summary_large_image"', $content);
        $this->assertStringContainsString('images/disdikpora-logo.svg', $content);
    }

    public function test_berita_og_type_is_article_once_no_duplicate_default(): void
    {
        $news = News::create([
            'title' => 'Berita Single',
            'slug' => 'berita-single',
            'excerpt' => 'Ringkasan',
            'content' => '<p>Isi</p>',
            'status' => News::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $content = $this->get(route('berita.show', $news->slug))->assertOk()->getContent();

        $this->assertSame(1, substr_count($content, 'property="og:type"'));
        $this->assertStringContainsString('property="og:type" content="article"', $content);
        $this->assertStringNotContainsString('property="og:type" content="website"', $content);
    }

    public function test_sitemap_is_cached_with_public_key(): void
    {
        $this->get('/sitemap.xml')->assertOk();

        $this->assertTrue($this->taggedHas([PublicCache::TAG_SITEMAP], PublicCache::SITEMAP));
    }
}
