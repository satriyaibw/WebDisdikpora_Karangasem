<?php

namespace App\Models\Traits;

/**
 * Format ukuran berkas (byte) menjadi bacaan manusiawi.
 *
 * Dipakai bersama oleh model ber-upload (PPID, Layanan, SOP, Unduhan)
 * agar pemformatan konsisten di seluruh panel admin.
 */
trait FormatsFileSize
{
    /**
     * Format ukuran berkas (byte) menjadi bacaan manusiawi.
     */
    public static function formatFileSize(?int $bytes): string
    {
        if ($bytes === null || $bytes < 0) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $power = $bytes > 0 ? (int) floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);

        $value = $bytes / (1024 ** $power);

        return number_format($value, $power > 0 ? 1 : 0).' '.$units[$power];
    }
}
