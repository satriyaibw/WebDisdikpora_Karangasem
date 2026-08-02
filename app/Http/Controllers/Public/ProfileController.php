<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Bidang;

class ProfileController extends Controller
{
    public function index()
    {
        return view('pages.profil');
    }

    public function struktur()
    {
        $bidangs = Bidang::orderBy('name')->get();

        return view('pages.profil-struktur', compact('bidangs'));
    }
}
