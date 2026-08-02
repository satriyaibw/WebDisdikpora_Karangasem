<?php

namespace App\Providers;

use App\Models\Agenda;
use App\Models\Album;
use App\Models\AlbumPhoto;
use App\Models\Announcement;
use App\Models\Bidang;
use App\Models\Category;
use App\Models\DownloadCategory;
use App\Models\DownloadFile;
use App\Models\Infographic;
use App\Models\News;
use App\Models\PpidCategory;
use App\Models\PpidDocument;
use App\Models\ProfileSection;
use App\Models\Service;
use App\Models\Slider;
use App\Models\SopDocument;
use App\Models\Video;
use App\Support\PublicCache;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPublicCacheInvalidation();

        $this->registerRateLimiters();
    }

    /**
     * Pasang invalidasi cache publik saat data diubah/dihapus (Fase 7.1).
     *
     * Tiap model memetakan tag domain + key statis. `PublicCache::forget`
     * mem-flush tag (membersihkan seluruh daftar paginated/search di
     * bawahnya) bila store mendukung tag, dan forget key statis sebagai
     * fallback store tanpa tag. Nilai closure dihitung per-record (mis.
     * key foto untuk `album_id` lama & baru).
     */
    private function registerPublicCacheInvalidation(): void
    {
        $map = [
            Slider::class => ['tags' => [PublicCache::TAG_HOME, PublicCache::TAG_SITEMAP], 'keys' => [PublicCache::HOME_SLIDERS]],
            Announcement::class => ['tags' => [PublicCache::TAG_HOME, PublicCache::TAG_ANNOUNCEMENTS, PublicCache::TAG_SITEMAP], 'keys' => [PublicCache::HOME_RUNNING_TEXTS]],
            News::class => ['tags' => [PublicCache::TAG_NEWS, PublicCache::TAG_HOME, PublicCache::TAG_SITEMAP], 'keys' => [PublicCache::HOME_LATEST_NEWS, PublicCache::NEWS_CATEGORIES]],
            Category::class => ['tags' => [PublicCache::TAG_NEWS, PublicCache::TAG_HOME, PublicCache::TAG_SITEMAP], 'keys' => [PublicCache::NEWS_CATEGORIES]],
            Agenda::class => ['tags' => [PublicCache::TAG_AGENDA, PublicCache::TAG_HOME, PublicCache::TAG_SITEMAP], 'keys' => [PublicCache::HOME_UPCOMING_AGENDAS, PublicCache::AGENDA_FINISHED]],
            Infographic::class => ['tags' => [PublicCache::TAG_HOME, PublicCache::TAG_SITEMAP], 'keys' => [PublicCache::HOME_INFOGRAPHICS]],
            Video::class => ['tags' => [PublicCache::TAG_GALERI, PublicCache::TAG_HOME, PublicCache::TAG_SITEMAP], 'keys' => [PublicCache::HOME_VIDEOS, PublicCache::GALERI_VIDEOS]],
            ProfileSection::class => ['tags' => [PublicCache::TAG_PROFILE, PublicCache::TAG_SITEMAP], 'keys' => [PublicCache::PROFILE_SECTIONS]],
            Bidang::class => ['tags' => [PublicCache::TAG_SERVICES, PublicCache::TAG_SOPS, PublicCache::TAG_SITEMAP], 'keys' => [PublicCache::SERVICES_BIDANGS, PublicCache::SOPS_BIDANGS]],
            Service::class => ['tags' => [PublicCache::TAG_SERVICES, PublicCache::TAG_SITEMAP], 'keys' => []],
            SopDocument::class => ['tags' => [PublicCache::TAG_SOPS, PublicCache::TAG_SITEMAP], 'keys' => []],
            PpidCategory::class => ['tags' => [PublicCache::TAG_PPID, PublicCache::TAG_SITEMAP], 'keys' => [PublicCache::PPID_CATEGORIES, 'ppid.first_category_slug']],
            PpidDocument::class => ['tags' => [PublicCache::TAG_PPID, PublicCache::TAG_SITEMAP], 'keys' => [PublicCache::PPID_CATEGORIES]],
            DownloadCategory::class => ['tags' => [PublicCache::TAG_DOWNLOADS, PublicCache::TAG_SITEMAP], 'keys' => [PublicCache::DOWNLOADS_GROUPS]],
            DownloadFile::class => ['tags' => [PublicCache::TAG_DOWNLOADS, PublicCache::TAG_SITEMAP], 'keys' => [PublicCache::DOWNLOADS_GROUPS]],
            Album::class => ['tags' => [PublicCache::TAG_GALERI, PublicCache::TAG_SITEMAP], 'keys' => []],
            AlbumPhoto::class => fn ($record): array => [
                'tags' => [PublicCache::TAG_GALERI],
                'keys' => array_values(array_filter([
                    $record->album_id ? 'galeri.album.photos.'.$record->album_id : null,
                    $record->getOriginal('album_id') ? 'galeri.album.photos.'.$record->getOriginal('album_id') : null,
                ])),
            ],
        ];

        foreach ($map as $model => $configuration) {
            foreach (['saved', 'deleted'] as $event) {
                $model::{$event}(static function ($record) use ($configuration): void {
                    $resolved = is_callable($configuration)
                        ? $configuration($record)
                        : $configuration;

                    PublicCache::forget($resolved['keys'] ?? [], $resolved['tags'] ?? []);
                });
            }
        }
    }

    /**
     * Rate limiter untuk rute POST publik masa depan (mis. form pengaduan).
     *
     * Login Filament memakai throttle bawaan (`WithRateLimiting`, 5 percobaan
     * per menit) sehingga tidak perlu diduplikasi di sini.
     */
    private function registerRateLimiters(): void
    {
        RateLimiter::for('public', static fn (Request $request) => Limit::perMinute(6)->by($request->ip() ?? 'guest'));
    }
}