<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdatePlayerJerseyNumberRequest;
use App\Models\Player;
use App\Models\SeasonRoster;

class SeasonRosterController extends Controller
{
    public function updatePlayerJerseyNumber(UpdatePlayerJerseyNumberRequest $request, SeasonRoster $roster, Player $player)
    {
        $validated = $request->validated();

        $roster->players()->updateExistingPivot($player->id, [
            'jersey_number' => $validated['jersey_number'],
        ]);

        return response()->json(['message' => 'Jersey number updated successfully.']);
    }
}