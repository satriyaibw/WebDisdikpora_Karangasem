<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hardening keamanan dasar (Fase 7.2).
 *
 * Memberlakukan security headers pada SEMUA respons grup `web`
 * (halaman publik + panel admin Filament). Nilai CSP dikonfigurasi
 * lewat `config/security.php`; source khusus Vite Dev Server (HMR)
 * hanya ditambahkan saat aplikasi berjalan lokal (file `public/hot`
 * dari `npm run dev`), sehingga kebijakan produksi tidak longgar.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if (config('security.csp_enabled')) {
            $response->headers->set('Content-Security-Policy', $this->buildCsp());
        }

        return $response;
    }

    /**
     * Susun CSP: base dari config + source Vite dev bila lokal/HMR aktif.
     */
    private function buildCsp(): string
    {
        $csp = (string) config('security.csp');

        if (! $this->isViteDevActive()) {
            return $csp;
        }

        $devSources = implode(' ', (array) config('security.csp_dev_sources', []));

        if ($devSources === '') {
            return $csp;
        }

        return (string) preg_replace(
            '/(\b(?:script-src|connect-src)\s+[^;]+)(;)/',
            '$1 '.$devSources.'$2',
            $csp,
        );
    }

    /**
     * Apakah Vite Dev Server sedang berjalan (file `public/hot` ada).
     */
    private function isViteDevActive(): bool
    {
        return app()->isLocal() && File::exists(public_path('hot'));
    }
}
