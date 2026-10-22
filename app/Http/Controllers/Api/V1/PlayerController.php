<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePlayerRequest;
use App\Http\Resources\V1\PlayerCollection;
use App\Http\Resources\V1\PlayerResource;
use App\Http\Resources\V1\PlayerSeasonStatResource;
use App\Models\Player;
use App\Models\PlayerSeasonStat;
use Illuminate\Http\Request;
use App\Http\Requests\Api\V1\UpdatePlayerRequest; 

class PlayerController extends Controller
{
    public function index(Request $request)
    {
        $query = Player::with(['seasonRosters.team', 'seasonRosters.season']);

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            
            // Поиск по имени, фамилии или их комбинации
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('last_name', 'like', "%{$searchTerm}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$searchTerm}%"])
                  ->orWhereRaw("CONCAT(last_name, ' ', first_name) LIKE ?", ["%{$searchTerm}%"]);
            });
        }
        
        $perPage = $request->input('per_page', 15);
        return new PlayerCollection($query->paginate($perPage));
    }

    public function store(StorePlayerRequest $request)
    {
        $validatedData = $request->validated();

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('players-photos', 'public');
            $validatedData['photo'] = $path;
        }

        $player = Player::create($validatedData);

        return (new PlayerResource($player))
                ->response()
                ->setStatusCode(201);
    }

    public function show(Player $player)
    {
        return new PlayerResource($player);
    }

    public function stats(Player $player)
    {
        $stats = PlayerSeasonStat::where('player_id', $player->id)
            ->with(['season.tournament', 'team'])
            ->get();

        return PlayerSeasonStatResource::collection($stats);
    }
	
	public function update(UpdatePlayerRequest $request, Player $player)
    {
        $validatedData = $request->validated();

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('players-photos', 'public');
            $validatedData['photo'] = $path;
        }

        $player->update($validatedData);

        return new PlayerResource($player);
    }
}