<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Settings
{
    /**
     * Ambil nilai setting dari tabel `settings` dengan cache 1 jam.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        return Cache::remember(
            'settings.'.$key,
            now()->addHour(),
            static fn (): ?string => DB::table('settings')->where('key', $key)->value('value') ?? $default
        );
    }

    /**
     * Hapus semua cache setting (dipanggil saat admin memperbarui pengaturan).
     */
    public static function flush(): void
    {
        Cache::forget('settings.*');
    }
}
