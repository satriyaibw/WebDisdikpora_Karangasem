<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Support\PublicCache;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $listKey = PublicCache::keyFor('announcements', ['page' => $request->input('page', 1)]);

        $announcements = PublicCache::remember($listKey, fn () => Announcement::published()
            ->latest('announcement_date')
            ->paginate(10), [PublicCache::TAG_ANNOUNCEMENTS]);

        return view('pages.pengumuman', compact('announcements'));
    }
}
