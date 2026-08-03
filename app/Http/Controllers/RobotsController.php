<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\Response;

/**
 * Menghasilkan `robots.txt` dinamis (Fase 7.3).
 *
 * `public/robots.txt` statis dihapus karena merujuk `http://localhost`.
 * Sebagai gantinya, `Sitemap` memakai `APP_URL` sehingga benar di semua
 * lingkungan. `Cache-Control: no-cache` karena nilai bergantung config.
 *
 * Catatan: hapus `location = /robots.txt` di `docker/nginx/default.conf`
 * agar permintaan `/robots.txt` diteruskan ke PHP.
 */
class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $content = implode("\n", [
            'User-agent: *',
            'Disallow: /admin',
            'Sitemap: '.rtrim((string) config('app.url'), '/').'/sitemap.xml',
        ]);

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'no-store, max-age=0',
        ]);
    }
}
