<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Announcement;
use App\Models\Infographic;
use App\Models\News;
use App\Models\Slider;
use App\Models\Video;
use App\Support\YouTube;

class HomeController extends Controller
{
    public function __invoke()
    {
        $sliders = Slider::active()->orderBy('sort_order')->get();

        $runningTexts = Announcement::published()
            ->where('is_important', true)
            ->latest('announcement_date')
            ->limit(5)
            ->get()
            ->whenEmpty(fn ($collection) => Announcement::published()
                ->latest('announcement_date')
                ->limit(5)
                ->get());

        $latestNews = News::published()
            ->with(['category', 'author'])
            ->orderByRaw('published_at IS NULL, published_at DESC')
            ->limit(6)
            ->get();

        $upcomingAgendas = Agenda::whereDate('date', '>=', today())
            ->orderBy('date')
            ->limit(4)
            ->get();

        $infographics = Infographic::active()->get();

        $videos = Video::published()
            ->latest()
            ->limit(3)
            ->get()
            ->map(function (Video $video) {
                $video->youtube_id = YouTube::parseId($video->youtube_url);

                return $video;
            })
            ->filter(fn (Video $video) => $video->youtube_id !== null);

        return view('pages.home', compact(
            'sliders',
            'runningTexts',
            'latestNews',
            'upcomingAgendas',
            'infographics',
            'videos'
        ));
    }
}
