<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Support\Facades\Storage;

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
        $service->hasFormTemplate = $service->form_template !== null
            && Storage::disk('public')->exists($service->form_template);

        return view('pages.layanan-show', compact('service'));
    }
}
