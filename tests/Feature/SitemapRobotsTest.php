<?php

namespace Tests\Feature;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapRobotsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_sitemap_returns_valid_xml_with_static_and_dynamic_urls(): void
    {
        // Item dinamis yang seharusnya muncul di sitemap.
        $news = News::create([
            'title' => 'Berita Sitemap Uji',
            'slug' => 'berita-sitemap-uji',
            'excerpt' => 'Ringkasan',
            'content' => '<p>Isi</p>',
            'status' => News::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');

        $content = $response->getContent();
        $this->assertStringStartsWith('<?xml version="1.0"', $content);
        $this->assertStringContainsString('<urlset', $content);
        $this->assertStringContainsString('<loc>'.route('home'), $content);
        $this->assertStringContainsString('<loc>'.route('berita.show', $news->slug), $content);
        $this->assertStringContainsString('<lastmod>', $content);
    }

    public function test_sitemap_uses_application_xml_content_type(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml');
    }

    public function test_robots_txt_points_to_sitemap(): void
    {
        $this->assertFileExists(public_path('robots.txt'));

        $content = file_get_contents(public_path('robots.txt'));
        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Disallow: /admin', $content);
        $this->assertStringContainsString('Sitemap: '.url('sitemap.xml'), $content);
    }
}