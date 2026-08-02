<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ProfileSection;

class ProfileController extends Controller
{
    public function index()
    {
        $sections = ProfileSection::active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('pages.profil', compact('sections'));
    }

    public function struktur()
    {
        return view('pages.profil-struktur');
    }
}
