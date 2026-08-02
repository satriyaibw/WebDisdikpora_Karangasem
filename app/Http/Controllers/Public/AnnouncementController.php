<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Announcement;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::published()
            ->latest('announcement_date')
            ->paginate(10);

        return view('pages.pengumuman', compact('announcements'));
    }
}
