<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Query caching untuk data publik (Fase 7.1).
 *
 * Semua nilai disimpan di driver cache (Redis saat runtime, array saat
 * test) dengan prefix skema key `public.`. Key statis di-forget persis
 * oleh invalidasi berbasis Eloquent model event saat admin menyimpan,
 * sedangkan key daftar yang dipaginasi/filter cukup andalkan TTL agar
 * data tidak pernah basi melebihi TTL (default 10 menit).
 */
class PublicCache
{
    /**
     * TTL default (detik) — 10 menit.
     */
    public const TTL = 600;

    /**
     * TTL untuk sitemap (detik) — 1 jam.
     */
    public const TTL_SITEMAP = 3600;

    /* ----------------- Key statis (dipakai reader & invalidator) ----------------- */

    public const HOME_SLIDERS = 'home.sliders';

    public const HOME_RUNNING_TEXTS = 'home.running_texts';

    public const HOME_LATEST_NEWS = 'home.latest_news';

    public const HOME_UPCOMING_AGENDAS = 'home.upcoming_agendas';

    public const HOME_INFOGRAPHICS = 'home.infographics';

    public const HOME_VIDEOS = 'home.videos';

    public const NEWS_CATEGORIES = 'news.categories';

    public const AGENDA_FINISHED = 'agenda.finished';

    public const DOWNLOADS_GROUPS = 'downloads.groups';

    public const PROFILE_SECTIONS = 'profile.sections';

    public const GALERI_VIDEOS = 'galeri.videos';

    public const SERVICES_BIDANGS = 'services.bidangs';

    public const SOPS_BIDANGS = 'sops.bidangs';

    public const PPID_CATEGORIES = 'ppid.categories';

    public const SITEMAP = 'sitemap';

    /**
     * Ambil hasil query dengan cache (wrap `Cache::remember`).
     */
    public static function remember(string $key, Closure $builder, ?int $ttl = null): mixed
    {
        return Cache::remember('public.'.$key, $ttl ?? (int) config('security.public_cache_ttl', self::TTL), $builder);
    }

    /**
     * Hapus satu/beberapa key cache publik secara presisi (bukan flush global).
     */
    public static function forget(string|array $keys): void
    {
        foreach ((array) $keys as $key) {
            Cache::forget('public.'.$key);
        }
    }

    /**
     * Buat key daftar yang unik terhadap parameter dinamis (page/search/filter).
     */
    public static function keyFor(string $prefix, array $parts = []): string
    {
        $normalized = array_map(static fn ($value): string => is_scalar($value)
            ? (string) $value
            : (string) json_encode($value), $parts);

        return $prefix.'.'.md5(implode('|', $normalized));
    }
}