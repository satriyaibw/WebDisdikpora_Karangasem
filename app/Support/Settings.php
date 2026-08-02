<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Settings
{
    /**
     * Cache key yang menampung seluruh baris tabel `settings`.
     *
     * Semua nilai disimpan dalam satu entry agar `flush()` benar-benar
     * bekerja (Cache::forget tidak mendukung wildcard) dan tidak terjadi
     * N query saat cold-cache (header + footer memanggil settings()
     * berkali-kali per halaman).
     */
    private const CACHE_KEY = 'settings';

    /**
     * TTL cache dalam detik (1 jam).
     */
    private const TTL = 3600;

    /**
     * Ambil nilai setting dari tabel `settings` dengan cache 1 jam.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $values = Cache::remember(
            self::CACHE_KEY,
            self::TTL,
            static fn (): array => DB::table('settings')
                ->select('key', 'value')
                ->get()
                ->mapWithKeys(static fn (object $row): array => [
                    $row->key => $row->value !== null ? (string) $row->value : null,
                ])
                ->all()
        );

        return $values[$key] ?? $default;
    }

    /**
     * Hapus cache setting (dipanggil saat admin memperbarui pengaturan).
     */
    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
