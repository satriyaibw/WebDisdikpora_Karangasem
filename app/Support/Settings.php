<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

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
            static fn (): array => Setting::query()
                ->select('key', 'value')
                ->get()
                ->mapWithKeys(static fn (Setting $setting): array => [
                    $setting->key => $setting->value,
                ])
                ->all()
        );

        return $values[$key] ?? $default;
    }

    /**
     * Simpan (atau perbarui) satu setting dan invalidasi cache.
     */
    public static function set(string $key, ?string $value, string $group = 'general'): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        self::flush();
    }

    /**
     * Hapus cache setting (dipanggil saat admin memperbarui pengaturan).
     */
    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
