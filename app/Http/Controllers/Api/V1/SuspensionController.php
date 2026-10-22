<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Suspension;
use App\Http\Resources\V1\SuspensionResource;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SuspensionController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Suspension::class);

        $suspensions = Suspension::with([
                'player', 
                // Загружаем заявки игрока, чтобы найти его текущую команду в этом сезоне
                'player.seasonRosters.team',
                'season.tournament', 
                'game.homeTeam', 
                'game.awayTeam',
                'game.round' // <-- Добавили загрузку Тура
            ])
            ->where('is_active', true)
            ->latest()
            ->get(); 

        return SuspensionResource::collection($suspensions);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Suspension::class);

        $validated = $request->validate([
            'player_id' => 'required|exists:players,id',
            'season_id' => 'required|exists:seasons,id',
            'reason' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'game_id' => 'nullable|exists:games,id',
        ]);

        $suspension = Suspension::create(array_merge($validated, [
            'is_active' => true,
            'served' => 0,
        ]));

        $suspension->load(['player', 'season.tournament', 'game.homeTeam', 'game.awayTeam']);

        return new SuspensionResource($suspension);
    }

    public function destroy(Suspension $suspension)
    {
        $this->authorize('delete', $suspension);
        $suspension->delete();
        return response()->json(['message' => __('api.suspension_deleted')]);
    }
}