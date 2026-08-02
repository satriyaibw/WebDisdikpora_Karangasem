<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Video;
use App\Support\YouTube;

class GalleryController extends Controller
{
    public function index()
    {
        $albums = Album::withCount('photos')
            ->with('coverPhoto')
            ->latest()
            ->paginate(9);

        $videos = Video::published()
            ->latest()
            ->get()
            ->map(function (Video $video) {
                $video->youtube_id = YouTube::parseId($video->youtube_url);

                return $video;
            })
            ->filter(fn (Video $video) => $video->youtube_id !== null)
            ->take(6);

        return view('pages.galeri', compact('albums', 'videos'));
    }

    public function show(Album $album)
    {
        $album->loadCount('photos');
        $album->load(['photos' => fn ($query) => $query->orderBy('sort_order')->orderBy('id')]);

        return view('pages.galeri-show', compact('album'));
    }
}
