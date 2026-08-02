<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class PpidController extends Controller
{
    public function index()
    {
        return view('pages.ppid');
    }
}
