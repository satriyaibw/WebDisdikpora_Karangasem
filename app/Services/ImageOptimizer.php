<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Kompresi/konversi gambar otomatis ke format WebP (MasterPlan 3.1).
 *
 * Implementasi memakai GD native PHP (tersedia di Docker app & PHP lokal),
 * tanpa dependency tambahan. Mengatur orientasi EXIF, memperkecil gambar
 * bila melebihi dimensi maksimal, lalu menyimpan sebagai `.webp` ke disk
 * `public` agar dapat diakses langsung dari frontend (Fase 6).
 */
class ImageOptimizer
{
    /**
     * Dimensi terpanjang (pixel) setelah resize.
     */
    public const MAX_DIMENSION = 1920;

    /**
     * Kualitas WebP (0-100).
     */
    public const QUALITY = 80;

    /**
     * Konversi file upload menjadi WebP dan simpan ke disk public.
     *
     * @return string path relatif hasil (contoh: images/berita/xxxx.webp)
     *
     * @throws InvalidArgumentException bila file bukan gambar yang didukung
     */
    public static function convertToWebp(UploadedFile $file, string $directory): string
    {
        $sourcePath = $file->getRealPath();

        if (! is_string($sourcePath) || ! is_file($sourcePath)) {
            throw new InvalidArgumentException('File gambar tidak valid.');
        }

        $image = @imagecreatefromstring((string) file_get_contents($sourcePath));

        if ($image === false) {
            throw new InvalidArgumentException('Format gambar tidak didukung. Gunakan JPG, PNG, atau WebP.');
        }

        try {
            $image = static::fixOrientation($image, $sourcePath);
            $image = static::resizeToMaxDimension($image, static::MAX_DIMENSION);

            $filename = Str::uuid()->toString().'.webp';
            $path = 'images/'.trim($directory, '/').'/'.$filename;

            Storage::disk('public')->makeDirectory(dirname($path));

            if (! imagewebp($image, Storage::disk('public')->path($path), static::QUALITY)) {
                throw new InvalidArgumentException('Gagal mengompresi gambar ke format WebP.');
            }
        } finally {
            imagedestroy($image);
        }

        return $path;
    }

    /**
     * Terapkan rotasi EXIF (foto dari HP sering kali menyimpan orientasi di metadata).
     */
    private static function fixOrientation(\GdImage $image, string $sourcePath): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($sourcePath);

        if ($exif === false) {
            return $image;
        }

        $orientation = (int) ($exif['Orientation'] ?? 1);

        switch ($orientation) {
            case 2:
                imageflip($image, IMG_FLIP_HORIZONTAL);
                break;
            case 3:
                $image = imagerotate($image, 180, 0);
                break;
            case 4:
                imageflip($image, IMG_FLIP_VERTICAL);
                break;
            case 5:
                $image = imagerotate($image, 270, 0);
                imageflip($image, IMG_FLIP_HORIZONTAL);
                break;
            case 6:
                $image = imagerotate($image, -90, 0);
                break;
            case 7:
                $image = imagerotate($image, 90, 0);
                imageflip($image, IMG_FLIP_HORIZONTAL);
                break;
            case 8:
                $image = imagerotate($image, 90, 0);
                break;
        }

        return $image;
    }

    /**
     * Perkecil gambar bila melebihi dimensi maksimal (rasio tetap).
     */
    private static function resizeToMaxDimension(\GdImage $image, int $maxDimension): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= $maxDimension && $height <= $maxDimension) {
            return $image;
        }

        $scale = min($maxDimension / $width, $maxDimension / $height);
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        // Pertahankan transparansi (PNG) agar hasil WebP tidak berlatar hitam.
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagefill($resized, 0, 0, imagecolorallocatealpha($resized, 255, 255, 255, 127));

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        imagedestroy($image);

        return $resized;
    }
}
