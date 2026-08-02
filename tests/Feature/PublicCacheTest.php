<?php

namespace Tests\Feature;

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

    public function test_homepage_queries_are_stored_in_public_cache(): void
    {
        $this->get(route('home'))->assertOk();

        $this->assertTrue(Cache::has('public.'.PublicCache::HOME_SLIDERS));
        $this->assertTrue(Cache::has('public.'.PublicCache::HOME_LATEST_NEWS));
        $this->assertTrue(Cache::has('public.'.PublicCache::HOME_RUNNING_TEXTS));
        $this->assertTrue(Cache::has('public.'.PublicCache::HOME_UPCOMING_AGENDAS));
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

    public function test_homepage_builds_open_graph_meta(): void
    {
        $response = $this->get(route('home'))->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('property="og:site_name"', $content);
        $this->assertStringContainsString('property="og:title"', $content);
        $this->assertStringContainsString('property="og:image"', $content);
        $this->assertStringContainsString('name="twitter:card" content="summary_large_image"', $content);
        $this->assertStringContainsString('images/disdikpora-logo.svg', $content);
    }

    public function test_sitemap_is_cached_with_public_key(): void
    {
        $this->get('/sitemap.xml')->assertOk();

        $this->assertTrue(Cache::has('public.'.PublicCache::SITEMAP));
    }
}