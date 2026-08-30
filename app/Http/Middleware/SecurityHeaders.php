<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
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
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('csp_nonce', $nonce);
        // Laravel Vite: otomatis tambah nonce ke <script>/<style> via @vite, dan Livewire via Vite::cspNonce() — docs: laravel/docs vite.md
        Vite::useCspNonce($nonce);

        // Best practice untuk quick tunnel: pakai host & scheme dari request
        // biar asset()/url()/Storage::url() tidak hardcode http://localhost
        // (sebelum PR memang manual ganti APP_URL tiap tunnel baru).
        $host = $request->getHost();
        if (str_ends_with($host, '.trycloudflare.com')) {
            // Quick tunnel selalu https — paksa https biar tidak mixed (sebelumnya
            // getScheme() bisa http jika TRUSTED_PROXIES belum include 127.0.0.1)
            $scheme = 'https';
            $root = $scheme.'://'.$host;
            URL::forceRootUrl($root);
            URL::forceScheme($scheme);
            // ponytail: override config runtime agar canonical/asset ikut tunnel
            config(['app.url' => $root]);
        }

        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if (config('security.csp_enabled')) {
            $response->headers->set('Content-Security-Policy', $this->buildCsp($request));
        }

        // Inject nonce ke semua <script> tanpa nonce (Filament assets, inline Alpine, dll) — ponytail: satu titik vs publish banyak view vendor
        if ($this->shouldInjectNonce($response)) {
            $content = $response->getContent();
            if (is_string($content) && str_contains($content, '<script')) {
                $content = $this->injectNonceIntoScripts($content, $nonce);
                $response->setContent($content);
            }
        }

        return $response;
    }

    /**
     * Susun CSP: base dari config + nonce + source Vite dev bila lokal/HMR aktif.
     */
    private function buildCsp(Request $request): string
    {
        $csp = (string) config('security.csp');
        $nonce = $request->attributes->get('csp_nonce');

        if (is_string($nonce) && $nonce !== '') {
            // Livewire 3 + Alpine butuh 'unsafe-eval' untuk AsyncFunction/new Function
            // (console: "Evaluating a string as JavaScript violates ... unsafe-eval").
            // PR 28 hapus keduanya jadi 'nonce-xxx' saja → Livewire error. Keep unsafe-eval.
            $csp = str_replace("'unsafe-inline' 'unsafe-eval'", "'nonce-{$nonce}' 'unsafe-eval'", $csp);
            // Fallback bila format CSP berubah: ganti sisa unsafe-inline di style-src dengan nonce juga? ponytail: biarkan style-src unsafe-inline untuk Tailwind/Filament
        }

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

    private function shouldInjectNonce(Response $response): bool
    {
        $type = $response->headers->get('Content-Type') ?? '';

        return $type === '' || str_contains($type, 'text/html');
    }

    /**
     * Tambah nonce ke <script> yang belum punya nonce — idempotent, tidak dobel.
     */
    private function injectNonceIntoScripts(string $html, string $nonce): string
    {
        return (string) preg_replace_callback(
            '/<script\b(?![^>]*\bnonce=)[^>]*>/i',
            static fn (array $m): string => rtrim($m[0], '>').' nonce="'.$nonce.'">',
            $html,
        );
    }
}
