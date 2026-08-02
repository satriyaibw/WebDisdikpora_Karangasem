<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Video;
use App\Support\PublicCache;
use App\Support\YouTube;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $albumsKey = PublicCache::keyFor('galeri.albums', ['page' => $request->input('page', 1)]);

        $albums = PublicCache::remember($albumsKey, fn () => Album::withCount('photos')
            ->with('coverPhoto')
            ->latest()
            ->paginate(9));

        $videos = PublicCache::remember(PublicCache::GALERI_VIDEOS, fn () => Video::published()
            ->latest()
            ->get()
            ->map(function (Video $video) {
                $video->youtube_id = YouTube::parseId($video->youtube_url);

                return $video;
            })
            ->filter(fn (Video $video) => $video->youtube_id !== null)
            ->take(6));

        return view('pages.galeri', compact('albums', 'videos'));
    }

    public function show(Album $album)
    {
        $album->loadCount('photos');

        $photos = PublicCache::remember('galeri.album.photos.'.$album->getKey(), fn () => $album->photos()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get());

        $album->setRelation('photos', $photos);

        return view('pages.galeri-show', compact('album'));
    }
}
