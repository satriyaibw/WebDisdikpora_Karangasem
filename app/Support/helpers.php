<?php

use App\Support\Settings;

if (! function_exists('settings')) {
    /**
     * Helper global untuk membaca tabel `settings` (dengan cache 1 jam).
     */
    function settings(string $key, ?string $default = null): ?string
    {
        return Settings::get($key, $default);
    }
}
