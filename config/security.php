<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Query Cache (Fase 7.1)
    |--------------------------------------------------------------------------
    |
    | TTL default untuk semua data publik yang dibungkus `PublicCache::remember`.
    | Key statis/turunan invalidasi presisi lewat model events + cache tags,
    | jadi nilai di sini hanyalah batas bawah (jaringan) bila store tanpa tag.
    |
    */

    'public_cache_ttl' => (int) env('PUBLIC_CACHE_TTL', 600),

    /*
    |--------------------------------------------------------------------------
    | Content-Security-Policy (Fase 7.2)
    |--------------------------------------------------------------------------
    |
    | `csp` adalah kebijakan dasar TANPA source khusus Vite dev server.
    | Middleware `SecurityHeaders` menambahkan `csp_dev_sources` ke
    | `script-src`/`connect-src` hanya saat aplikasi berjalan lokal
    | (Vite Dev Server / HMR aktif), sehingga produksi tidak longgar.
    |
    */

    'csp_enabled' => env('SECURITY_CSP_ENABLED', true),

    'csp' => env('SECURITY_CSP_POLICY', "default-src 'self'; "
        ."script-src 'self' 'unsafe-inline' 'unsafe-eval'; "
        ."style-src 'self' 'unsafe-inline'; "
        ."img-src 'self' data: blob: https:; "
        ."font-src 'self' data:; "
        ."connect-src 'self'; "
        // frame-src 'self': pratinjau PDF SOP di iframe same-origin (/storage/...).
        // object-src tetap 'none' — tidak ada <object>/<embed> di aplikasi.
        ."frame-src 'self' https://www.youtube-nocookie.com https://www.youtube.com https://www.google.com https://maps.google.com; "
        ."object-src 'none'; "
        ."base-uri 'self'; "
        ."form-action 'self'; "
        ."frame-ancestors 'self'"),

    /*
    |--------------------------------------------------------------------------
    | CSP Source untuk Vite Dev Server / HMR (hanya aktif saat lokal)
    |--------------------------------------------------------------------------
    |
    | Ditambahkan oleh middleware ke `script-src` dan `connect-src` bila
    | aplikasi berjalan di lingkungan lokal (Vite `hot` aktif).
    |
    */

    'csp_dev_sources' => [
        'http://localhost:*',
        'ws://localhost:*',
    ],

];
