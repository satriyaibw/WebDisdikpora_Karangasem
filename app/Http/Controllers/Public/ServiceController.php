<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;

class ServiceController extends Controller
{
    public function index()
    {
        return view('pages.layanan');
    }

    public function show(Service $service)
    {
        abort_unless($service->status === Service::STATUS_PUBLISHED, 404);

        $service->load('bidang');

        return view('pages.layanan-show', compact('service'));
    }
}
