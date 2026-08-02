<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hardening keamanan dasar (Fase 7.2).
 *
 * Memberlakukan security headers pada SEMUA respons grup `web`
 * (halaman publik + panel admin Filament). Nilai CSP dikonfigurasi
 * lewat `config/security.php` agar pengoperasi bisa menajamkan
 * kebijakan (mis. nonce) tanpa mengubah kode.
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
            $response->headers->set('Content-Security-Policy', (string) config('security.csp'));
        }

        return $response;
    }
}