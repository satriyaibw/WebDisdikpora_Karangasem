<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\News;
use App\Models\Service;
use App\Models\SopDocument;
use App\Support\PublicCache;
use Carbon\Carbon;

/**
 * Menghasilkan `sitemap.xml` dinamis (Fase 7.3).
 *
 * Daftar URL statis + item publik ber-status published dengan `<lastmod>`.
 * Hasil di-cache 1 jam dan di-*invalidate* oleh model events lewat
 * `PublicCache::forget('sitemap')`. Tidak membuat berkas statis duplikat.
 */
class SitemapController extends Controller
{
    public function __invoke()
    {
        $urls = PublicCache::remember(PublicCache::SITEMAP, fn (): array => [
            ...$this->staticUrls(),
            ...$this->newsUrls(),
            ...$this->serviceUrls(),
            ...$this->sopUrls(),
            ...$this->albumUrls(),
        ], [PublicCache::TAG_SITEMAP], PublicCache::TTL_SITEMAP);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.e($url['loc']).'</loc>'."\n";
            if (! empty($url['lastmod'])) {
                $xml .= '    <lastmod>'.$url['lastmod']->toAtomString().'</lastmod>'."\n";
            }
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml)->header('Content-Type', 'application/xml');
    }

    /**
     * URL statis portal publik.
     *
     * @return array<int, array{loc: string, lastmod?: Carbon}>
     */
    private function staticUrls(): array
    {
        return array_map(
            static fn (string $path): array => ['loc' => url($path)],
            ['', 'profil', 'profil/struktur', 'layanan', 'sop', 'ppid', 'berita', 'pengumuman', 'agenda', 'galeri', 'unduhan', 'kontak']
        );
    }

    /**
     * URL berita yang terbit.
     *
     * @return array<int, array{loc: string, lastmod: Carbon}>
     */
    private function newsUrls(): array
    {
        return News::published()
            ->select('slug', 'updated_at')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (News $news): array => [
                'loc' => route('berita.show', $news->slug),
                'lastmod' => $news->updated_at,
            ])
            ->all();
    }

    /**
     * URL layanan yang terbit.
     *
     * @return array<int, array{loc: string, lastmod: Carbon}>
     */
    private function serviceUrls(): array
    {
        return Service::query()
            ->where('status', Service::STATUS_PUBLISHED)
            ->select('slug', 'updated_at')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (Service $service): array => [
                'loc' => route('layanan.show', $service->slug),
                'lastmod' => $service->updated_at,
            ])
            ->all();
    }

    /**
     * URL dokumen SOP yang terbit.
     *
     * @return array<int, array{loc: string, lastmod: Carbon}>
     */
    private function sopUrls(): array
    {
        return SopDocument::query()
            ->where('status', SopDocument::STATUS_PUBLISHED)
            ->select('slug', 'updated_at')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (SopDocument $document): array => [
                'loc' => route('sop.show', $document->slug),
                'lastmod' => $document->updated_at,
            ])
            ->all();
    }

    /**
     * URL galeri album.
     *
     * @return array<int, array{loc: string, lastmod: Carbon}>
     */
    private function albumUrls(): array
    {
        return Album::query()
            ->select('id', 'updated_at')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (Album $album): array => [
                'loc' => route('galeri.show', $album),
                'lastmod' => $album->updated_at,
            ])
            ->all();
    }
}
