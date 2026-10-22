<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PersonCollection; 
use App\Http\Resources\V1\TeamResource;
use App\Http\Resources\V1\TeamCollection; 
use App\Models\Team;
use App\Http\Resources\V1\PlayerCollection; 
use App\Models\SeasonRoster; 
use Illuminate\Http\Request;
use App\Http\Requests\Api\V1\StoreSeasonRosterRequest; 
use App\Models\TransferRequest;
use App\Http\Resources\V1\TransferRequestCollection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; 

class TeamController extends Controller
{
    use AuthorizesRequests;
	
	public function index(Request $request)
    {
        $query = Team::query();
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        return new TeamCollection($query->paginate());
    }

    public function show(Team $team)
    {
        return new TeamResource($team);
    }

    public function staff(Team $team)
    {
        return new PersonCollection($team->staff()->with('teams')->paginate());
    }

    public function roster(Request $request, Team $team)
    {
        $request->validate(['season_id' => 'required|exists:seasons,id']);

        $roster = SeasonRoster::where('team_id', $team->id)
            ->where('season_id', $request->season_id)
            ->first();

        if (!$roster) {
            return response()->json(['data' => []]);
        }

        return new PlayerCollection($roster->players()->paginate());
    }
    
    public function storeRoster(StoreSeasonRosterRequest $request, Team $team)
    {
        $this->authorize('update', $team);

        $validated = $request->validated();

        $roster = SeasonRoster::firstOrCreate([
            'team_id' => $team->id,
            'season_id' => $validated['season_id'],
        ]);

        // --- ИСПРАВЛЕНИЕ: Работаем с массивом 'players', а не 'player_ids' ---
        $playersWithPivotData = collect($validated['players'])->mapWithKeys(function ($playerData) use ($validated) {
            return [
                $playerData['player_id'] => [
                    'jersey_number' => $playerData['jersey_number'] ?? 0,
                    'season_id' => $validated['season_id']
                ]
            ];
        });

        $roster->players()->sync($playersWithPivotData);

        return response()->json(['message' => 'Roster submitted successfully.'], 201);
    }

	public function transfers(Team $team)
    {
        $transfers = TransferRequest::where('from_team_id', $team->id)
            ->orWhere('to_team_id', $team->id)
            ->with(['player', 'fromTeam', 'toTeam'])
            ->latest()
            ->paginate();

        return new TransferRequestCollection($transfers);
    }
}