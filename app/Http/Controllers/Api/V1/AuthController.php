<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\GameCollection;
use App\Http\Resources\V1\TeamCollection;
use App\Http\Resources\V1\UserResource;
use App\Models\SeasonRoster;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('api.invalid_credentials')],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user)
        ]);
    }

    /**
     * Get the authenticated User.
     */
    public function user(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * Handle a logout request to the application.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => __('api.logged_out')]);
    }

    /**
     * Get the authenticated user's profile with role-specific data.
     */
    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => __('api.unauthenticated')], 401);
        }

        $data = ['user' => new UserResource($user)];

        if ($user->hasRole('Team Manager') && $user->person) {
            // --- НАЧАЛО НОВОЙ ЛОГИКИ ---
            $teams = $user->person->teams;

            $teams->each(function ($team) {
                // 1. Ищем самый актуальный СЕЗОН для этой команды
                $season = $team->seasons()->where('is_current', true)->first();

                // 2. Если текущего сезона нет, берем просто самый последний по дате
                if (!$season) {
                    $season = $team->seasons()->latest('start_date')->first();
                }

                // 3. Если сезон найден, прикрепляем его и ищем заявку именно для него
                if ($season) {
                    $team->setRelation('season', $season); // Прикрепляем сезон
                    $roster = $team->seasonRosters()
                        ->where('season_id', $season->id)
                        ->with(['players' => fn ($q) => $q->withPivot('jersey_number')])
                        ->first();
                    $team->setRelation('seasonRosters', $roster ? collect([$roster]) : collect());
                } else {
                    // Если команда вообще не заявлена ни на один сезон
                    $team->setRelation('season', null);
                    $team->setRelation('seasonRosters', collect());
                }
            });

            $data['teams'] = new TeamCollection($teams);
            // --- КОНЕЦ НОВОЙ ЛОГИКИ ---
        }

        if ($user->hasRole('Referee') && $user->person) {
            $games = $user->person->games()
                ->where('status', 'scheduled')
                ->with(['homeTeam', 'awayTeam'])
                ->orderBy('game_datetime', 'asc')
                ->get();
            $data['games'] = new GameCollection($games);
        }

        return response()->json($data);
    }
}