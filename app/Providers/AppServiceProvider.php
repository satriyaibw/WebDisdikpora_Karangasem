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
     * Hanya key statis yang di-forget secara presisi (bukan `cache()->flush()`
     * global). Daftar yang dipaginasi/dicari cukup bergantung pada TTL.
     * Nilai yang berupa closure dihitung per-record (mis. key album dari
     * `album_id` foto yang berubah).
     */
    private function registerPublicCacheInvalidation(): void
    {
        $map = [
            Slider::class => [PublicCache::HOME_SLIDERS, PublicCache::SITEMAP],
            Announcement::class => [PublicCache::HOME_RUNNING_TEXTS, PublicCache::SITEMAP],
            News::class => [PublicCache::HOME_LATEST_NEWS, PublicCache::NEWS_CATEGORIES, PublicCache::SITEMAP],
            Category::class => [PublicCache::NEWS_CATEGORIES, PublicCache::HOME_LATEST_NEWS, PublicCache::SITEMAP],
            Agenda::class => [PublicCache::HOME_UPCOMING_AGENDAS, PublicCache::AGENDA_FINISHED, PublicCache::SITEMAP],
            Infographic::class => [PublicCache::HOME_INFOGRAPHICS, PublicCache::SITEMAP],
            Video::class => [PublicCache::HOME_VIDEOS, PublicCache::GALERI_VIDEOS, PublicCache::SITEMAP],
            ProfileSection::class => [PublicCache::PROFILE_SECTIONS, PublicCache::SITEMAP],
            Bidang::class => [PublicCache::SERVICES_BIDANGS, PublicCache::SOPS_BIDANGS, PublicCache::SITEMAP],
            Service::class => [PublicCache::SITEMAP],
            SopDocument::class => [PublicCache::SITEMAP],
            PpidCategory::class => [PublicCache::PPID_CATEGORIES, 'ppid.first_category_slug', PublicCache::SITEMAP],
            PpidDocument::class => [PublicCache::PPID_CATEGORIES, PublicCache::SITEMAP],
            DownloadCategory::class => [PublicCache::DOWNLOADS_GROUPS, PublicCache::SITEMAP],
            DownloadFile::class => [PublicCache::DOWNLOADS_GROUPS, PublicCache::SITEMAP],
            Album::class => [PublicCache::SITEMAP],
            AlbumPhoto::class => fn ($record): array => $record->album_id
                ? ['galeri.album.photos.'.$record->album_id]
                : [],
        ];

        foreach ($map as $model => $configuration) {
            foreach (['saved', 'deleted'] as $event) {
                $model::{$event}(function ($record) use ($configuration) {
                    PublicCache::forget(is_callable($configuration)
                        ? $configuration($record)
                        : $configuration);
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