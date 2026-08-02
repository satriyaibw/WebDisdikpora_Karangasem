<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Official;

class ProfileController extends Controller
{
    public function index()
    {
        return view('pages.profil');
    }

    public function struktur()
    {
        $tree = Official::active()
            ->with([
                'children' => fn ($query) => $query->active(),
                'children.children' => fn ($query) => $query->active(),
            ])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('pages.profil-struktur', compact('tree'));
    }
}
