<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ProfileSection;
use App\Support\PublicCache;

class ProfileController extends Controller
{
    public function index()
    {
        $sections = PublicCache::remember(PublicCache::PROFILE_SECTIONS, fn () => ProfileSection::active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(), [PublicCache::TAG_PROFILE]);

        return view('pages.profil', compact('sections'));
    }

    public function struktur()
    {
        return view('pages.profil-struktur');
    }
}
