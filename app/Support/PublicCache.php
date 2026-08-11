<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Query caching untuk data publik (Fase 7.1, disempurnakan review).
 *
 * Semua nilai disimpan dengan prefix skema key `public.` serta di-tag
 * dengan domain (mis. `PublicCache::TAG_NEWS`). Invalidasi memakai
 * `Cache::tags(...)->flush()` sehingga daftar yang dipaginasi/search
 * ikut dibersihkan presisi saat model berubah — bukan menunggu TTL.
 *
 * Store `array`/`redis`/`memcached` mendukung tags (Laravel
 * `TaggableStore`); bila store tidak mendukung tag (mis. `file`/
 * `database`), metode ini jatuh ke key tunggal tak bertag dan key
 * turunan cukup mengandalkan TTL seperti semula.
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

    /* ----------------- Tag domain (reader & invalidator bersama) ----------------- */

    public const TAG_HOME = 'home';

    public const TAG_NEWS = 'news';

    public const TAG_AGENDA = 'agenda';

    public const TAG_GALERI = 'galeri';

    public const TAG_DOWNLOADS = 'downloads';

    public const TAG_PROFILE = 'profile';

    public const TAG_SERVICES = 'services';

    public const TAG_SOPS = 'sops';

    public const TAG_PPID = 'ppid';

    public const TAG_ANNOUNCEMENTS = 'announcements';

    public const TAG_SITEMAP = 'sitemap';

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
     *
     * @param  array<int, string>  $tags  tag domain (mis. [self::TAG_NEWS])
     */
    public static function remember(string $key, Closure $builder, array $tags = [], ?int $ttl = null): mixed
    {
        $ttl ??= (int) (config('security.public_cache_ttl', self::TTL));
        $fullKey = 'public.'.$key;

        if (empty($tags) || ! static::taggable()) {
            return Cache::remember($fullKey, $ttl, $builder);
        }

        return Cache::tags($tags)->remember($fullKey, $ttl, $builder);
    }

    /**
     * Hapus data publik. Saat store mendukung tag: flush tag domain
     * terlebih dahulu (membersihkan semua key turunan di bawahnya),
     * kemudian forget key statis secara presisi sebagai fallback.
     */
    public static function forget(string|array $keys, array $tags = []): void
    {
        if (! empty($tags) && static::taggable()) {
            Cache::tags($tags)->flush();
        }

        foreach ((array) $keys as $key) {
            Cache::forget('public.'.$key);
        }
    }

    /**
     * Buat key daftar yang unik terhadap parameter dinamis (page/search/filter).
     *
     * Input dinormalisasi (lowercase, trim, dibatasi panjangnya) agar
     * pencarian yang ekuivalen memakai satu slot cache dan tidak membuat
     * key tak terbatas (pengisi cache) dari masukan pengguna. Array di
     *-enkode dengan JSON (bukan implode delimitir) agar nilai yang
     * mengandung `|` tidak bentrok dengan pemisahan antar-part.
     */
    public static function keyFor(string $prefix, array $parts = []): string
    {
        $normalized = array_map(static fn ($value): string => self::normalizePart(
            is_scalar($value) ? (string) $value : (string) json_encode($value)
        ), $parts);

        return $prefix.'.'.md5(json_encode($normalized));
    }

    /**
     * Store saat ini mendukung cache tag (Laravel `TaggableStore`).
     */
    private static function taggable(): bool
    {
        $store = Cache::getStore();

        return is_object($store) && method_exists($store, 'tags');
    }

    /**
     * Normalisasi nilai untuk keperluan key cache publik.
     */
    private static function normalizePart(string $value): string
    {
        return Str::limit(
            mb_strtolower(trim($value)),
            80,
            ''
        ) ?? '';
    }
}
