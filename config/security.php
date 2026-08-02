<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Query Cache (Fase 7.1)
    |--------------------------------------------------------------------------
    |
    | TTL default untuk semua data publik yang dibungkus `PublicCache::remember`.
    | 600 = 10 menit. Key statis tetap di-forget persis lewat model events,
    | sedangkan daftar paginated cukup bergantung pada TTL ini.
    |
    */

    'public_cache_ttl' => env('PUBLIC_CACHE_TTL', 600),

    /*
    |--------------------------------------------------------------------------
    | Content-Security-Policy (Fase 7.2)
    |--------------------------------------------------------------------------
    |
    | Nilai kebijakan pragmatis yang tetap mengizinkan Vite (script module),
    | Livewire (script/style inline) dan Alpine (eval ekspresi). `localhost`
    | dicantumkan hanya untuk Vite Dev Server (HMR). Saat HTTPS/domain
    | produksi aktif (Fase 8), kebijakan dapat ditajamkan (nonce) dengan
    | mengganti nilai env ini — tidak perlu kode berubah.
    |
    */

    'csp_enabled' => env('SECURITY_CSP_ENABLED', true),

    'csp' => env('SECURITY_CSP_POLICY', "default-src 'self'; "
        ."script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:* ws://localhost:*; "
        ."style-src 'self' 'unsafe-inline'; "
        ."img-src 'self' data: blob: https:; "
        ."font-src 'self' data:; "
        ."connect-src 'self' http://localhost:* ws://localhost:* wss:; "
        ."frame-src https://www.youtube-nocookie.com https://www.youtube.com https://www.google.com https://maps.google.com; "
        ."object-src 'none'; "
        ."base-uri 'self'; "
        ."form-action 'self'; "
        ."frame-ancestors 'self'"),

];