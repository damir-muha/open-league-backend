<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Tournament;

class TournamentController extends Controller
{
    public function show(Tournament $tournament)
    {
        // Загружаем турнир вместе с его сезонами
        $tournament->load('seasons');
        return view('tournaments.show', compact('tournament'));
    }
}