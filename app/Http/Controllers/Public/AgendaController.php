<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Support\PublicCache;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    public function index(Request $request)
    {
        $upcomingKey = PublicCache::keyFor('agenda.upcoming', ['page' => $request->input('page', 1)]);

        $upcoming = PublicCache::remember($upcomingKey, fn () => Agenda::whereDate('date', '>=', today())
            ->orderBy('date')
            ->orderBy('start_time')
            ->paginate(15));

        $finished = PublicCache::remember(PublicCache::AGENDA_FINISHED, fn () => Agenda::whereDate('date', '<', today())
            ->orderByDesc('date')
            ->limit(6)
            ->get());

        return view('pages.agenda', compact('upcoming', 'finished'));
    }
}
