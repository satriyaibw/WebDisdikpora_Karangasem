<?php

namespace Tests\Unit;

use App\Services\ImageOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImageOptimizerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private const EXIF_JPEG_ORIENTATION_6 =
        '/9j/4AAQSkZJRgABAQAAAQABAAD/4QAiRXhpZgAATU0AKgAAAAgAAQESAAMAAAABAAYAAAAAAAD/'.
        '2wBDAAUDBAQEAwUEBAQFBQUGBwwIBwcHBw8LCwkMEQ8SEhEPERETFhwXExQaFRERGCEYGh0dHx8f'.
        'ExciJCIeJBweHx7/2wBDAQUFBQcGBw4ICA4eFBEUHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4e'.
        'Hh4eHh4eHh4eHh4eHh4eHh4eHh4eHh7/wAARCADIAGQDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEA'.
        'AAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJx'.
        'FDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNk'.
        'ZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJ'.
        'ytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQF'.
        'BgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMz'.
        'UvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3'.
        'eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna'.
        '4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwDyyiiivzo/ssKKKKACiiigAooooAKKKKAC'.
        'iiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKK'.
        'KKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooo'.
        'oAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiig'.
        'AooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKAC'.
        'iiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKK'.
        'KKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooo'.
        'oA//2Q=='
    ;

    #[Test]
    public function it_converts_uploaded_image_to_webp_on_public_disk(): void
    {
        $file = UploadedFile::fake()->image('sampul.jpg', 100, 80);

        $path = ImageOptimizer::convertToWebp($file, 'berita');

        $this->assertStringEndsWith('.webp', $path);
        $this->assertStringStartsWith('images/berita/', $path);
        $this->assertTrue(Storage::disk('public')->exists($path));

        $mime = mime_content_type(Storage::disk('public')->path($path));
        $this->assertEquals('image/webp', $mime);
    }

    #[Test]
    public function it_resizes_images_larger_than_max_dimension(): void
    {
        $file = UploadedFile::fake()->image('besar.png', 4000, 2000);

        $path = ImageOptimizer::convertToWebp($file, 'berita');

        [$width, $height] = getimagesize(Storage::disk('public')->path($path));

        $this->assertLessThanOrEqual(ImageOptimizer::MAX_DIMENSION, $width);
        $this->assertLessThanOrEqual(ImageOptimizer::MAX_DIMENSION, $height);
    }

    #[Test]
    public function it_keeps_small_images_unchanged_in_dimensions(): void
    {
        $file = UploadedFile::fake()->image('kecil.png', 50, 40);

        $path = ImageOptimizer::convertToWebp($file, 'berita');

        [$width, $height] = getimagesize(Storage::disk('public')->path($path));

        $this->assertEquals(50, $width);
        $this->assertEquals(40, $height);
    }

    #[Test]
    public function it_rejects_non_image_files(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ImageOptimizer::convertToWebp(UploadedFile::fake()->create('dokumen.pdf', 100), 'berita');
    }

    #[Test]
    public function it_rejects_files_larger_than_max_source_size(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ImageOptimizer::convertToWebp(UploadedFile::fake()->create('besar.jpg', 25000), 'berita');
    }

    #[Test]
    public function it_rejects_images_wider_than_max_source_dimension(): void
    {
        $source = imagecreatetruecolor(9000, 100);
        $tempPath = tempnam(sys_get_temp_dir(), 'img');
        imagepng($source, $tempPath);
        imagedestroy($source);

        try {
            $this->expectException(InvalidArgumentException::class);

            ImageOptimizer::convertToWebp(new UploadedFile($tempPath, 'raksasa.png', 'image/png'), 'berita');
        } finally {
            @unlink($tempPath);
        }
    }

    #[Test]
    public function it_accepts_webp_source_files(): void
    {
        $source = imagecreatetruecolor(10, 10);
        $tempPath = tempnam(sys_get_temp_dir(), 'webp');
        imagewebp($source, $tempPath, 80);
        imagedestroy($source);

        $file = new UploadedFile($tempPath, 'sumber.webp', 'image/webp');

        $path = ImageOptimizer::convertToWebp($file, 'galeri');

        $this->assertTrue(Storage::disk('public')->exists($path));

        @unlink($tempPath);
    }

    #[Test]
    public function it_applies_exif_orientation_to_phone_photos(): void
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'exif');
        file_put_contents($tempPath, base64_decode(self::EXIF_JPEG_ORIENTATION_6));

        try {
            $file = new UploadedFile($tempPath, 'foto-hp.jpg', 'image/jpeg');

            $path = ImageOptimizer::convertToWebp($file, 'berita');

            [$width, $height] = getimagesize(Storage::disk('public')->path($path));

            $this->assertEquals(200, $width);
            $this->assertEquals(100, $height);
        } finally {
            @unlink($tempPath);
        }
    }
}
