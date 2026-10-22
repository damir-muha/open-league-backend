<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Barryvdh\DomPDF\Facade\Pdf;

class ProtocolController extends Controller
{
    public function print(Game $game, string $type)
    {
        if (!in_array($type, ['pre_game', 'post_game'])) {
            abort(404);
        }

        // Загружаем все необходимые связи
        $game->loadMissing([
            'homeTeam', 'awayTeam', 'events.player', 'events.team',
            'lineups' => fn ($query) => $query->with('player')->orderBy('status', 'desc')
        ]);

        // Фильтруем заявку по командам и статусу (стартовый/запасной)
        $homeLineup = $game->lineups->where('team_id', $game->home_team_id);
        $awayLineup = $game->lineups->where('team_id', $game->away_team_id);

        $data = [
            'game' => $game,
            'homeStarters' => $homeLineup->where('starter', true),
            'homeSubs' => $homeLineup->where('starter', false),
            'awayStarters' => $awayLineup->where('starter', true),
            'awaySubs' => $awayLineup->where('starter', false),
        ];

        $fileName = "protocol_{$game->id}_{$type}.pdf";
        $pdf = Pdf::loadView("protocols.{$type}", $data);

        return $pdf->download($fileName);
    }
}
