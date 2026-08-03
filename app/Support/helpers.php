<?php

use App\Support\Settings;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

if (! function_exists('settings')) {
    /**
     * Helper global untuk membaca tabel `settings` (dengan cache 1 jam).
     */
    function settings(string $key, ?string $default = null): ?string
    {
        return Settings::get($key, $default);
    }
}

if (! function_exists('public_url_if_exists')) {
    /**
     * URL publik berkas bila berkas benar-benar ada di disk `public`,
     * selain itu null — menghindari link/gambar rusak saat berkas
     * dihapus dari disk tanpa memperbarui baris database.
     */
    function public_url_if_exists(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::disk('public')->exists($path)
            ? Storage::disk('public')->url($path)
            : null;
    }
}

if (! function_exists('public_download_response')) {
    /**
     * Respon unduhan asli (`Content-Disposition: attachment`) untuk berkas
     * di disk `public`, memakai nama asli berkas dari `$path`.
     * 404 bila path kosong atau berkas tidak ada di disk — mencegah
     * unduhan/pratinjau rusak saat berkas dihapus tanpa update baris.
     */
    function public_download_response(?string $path): Response
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->download($path);
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
