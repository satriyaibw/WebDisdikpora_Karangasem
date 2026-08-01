<?php

namespace Tests\Unit;

use App\Rules\ValidYouTubeUrl;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValidYouTubeUrlTest extends TestCase
{
    #[Test]
    public function it_accepts_common_youtube_url_formats(): void
    {
        $urls = [
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'https://youtube.com/watch?v=dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ',
            'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'https://www.youtube.com/shorts/dQw4w9WgXcQ',
            'https://m.youtube.com/watch?v=dQw4w9WgXcQ',
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=30s',
        ];

        foreach ($urls as $url) {
            $this->assertTrue(
                Validator::make(['url' => $url], ['url' => [new ValidYouTubeUrl]])->passes(),
                "Harus valid: {$url}"
            );
        }
    }

    #[Test]
    public function it_rejects_non_youtube_urls(): void
    {
        $urls = [
            'https://vimeo.com/12345',
            'https://www.google.com/watch?v=dQw4w9WgXcQ',
            'https://youtube.com.evil.example.com/watch?v=dQw4w9WgXcQ',
        ];

        foreach ($urls as $url) {
            $this->assertFalse(
                Validator::make(['url' => $url], ['url' => [new ValidYouTubeUrl]])->passes(),
                "Harus ditolak: {$url}"
            );
        }
    }

    #[Test]
    public function it_rejects_youtube_urls_without_a_video_id(): void
    {
        $urls = [
            'https://www.youtube.com/watch',
            'https://www.youtube.com/watch?v=',
            'https://youtu.be/',
            'https://www.youtube.com/embed/',
        ];

        foreach ($urls as $url) {
            $this->assertFalse(
                Validator::make(['url' => $url], ['url' => [new ValidYouTubeUrl]])->passes(),
                "Harus ditolak: {$url}"
            );
        }
    }

    #[Test]
    public function it_extracts_video_id_from_common_formats(): void
    {
        $this->assertEquals('dQw4w9WgXcQ', ValidYouTubeUrl::extractVideoId('https://www.youtube.com/watch?v=dQw4w9WgXcQ'));
        $this->assertEquals('dQw4w9WgXcQ', ValidYouTubeUrl::extractVideoId('https://youtu.be/dQw4w9WgXcQ'));
        $this->assertEquals('dQw4w9WgXcQ', ValidYouTubeUrl::extractVideoId('https://www.youtube.com/embed/dQw4w9WgXcQ'));
        $this->assertEquals('dQw4w9WgXcQ', ValidYouTubeUrl::extractVideoId('https://www.youtube.com/shorts/dQw4w9WgXcQ'));
        $this->assertNull(ValidYouTubeUrl::extractVideoId('https://www.youtube.com/watch'));
    }
}
