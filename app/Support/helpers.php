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

if (! function_exists('escapeLike')) {
    /**
     * Escape karakter wildcard SQL LIKE (`%`, `_`, `\`) dari input pencarian
     * agar diperlakukan sebagai teks literal, bukan wildcard.
     */
    function escapeLike(?string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], (string) $value);
    }
}
