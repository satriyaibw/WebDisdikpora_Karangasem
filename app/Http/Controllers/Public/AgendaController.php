<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Agenda;

class AgendaController extends Controller
{
    public function index()
    {
        $upcoming = Agenda::whereDate('date', '>=', today())
            ->orderBy('date')
            ->orderBy('start_time')
            ->paginate(15);

        $finished = Agenda::whereDate('date', '<', today())
            ->orderByDesc('date')
            ->limit(6)
            ->get();

        return view('pages.agenda', compact('upcoming', 'finished'));
    }
}
