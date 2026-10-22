<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Season;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSeasonRosterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('team'));
    }

    public function rules(): array
    {
        return [
            'season_id' => ['required', 'integer', 'exists:seasons,id'],
            'players' => ['required', 'array'], // Ожидаем массив 'players'
            'players.*.player_id' => ['required', 'integer', 'exists:players,id'],
            'players.*.jersey_number' => ['nullable', 'integer', 'min:0', 'max:99'], // Разрешаем 0 как номер по умолчанию
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $seasonId = $this->input('season_id');
            $players = $this->input('players', []);

            if (!$seasonId) return;

            $season = Season::find($seasonId);
            $minPlayers = $season->roster_min_players;
            $maxPlayers = $season->roster_max_players;
            $playerCount = count($players);

            if ($playerCount < $minPlayers) {
                $validator->errors()->add('players', __('api.roster_min_players', ['min' => $minPlayers, 'count' => $playerCount]));
            }

            if ($playerCount > $maxPlayers) {
                $validator->errors()->add('players', __('api.roster_max_players', ['max' => $maxPlayers, 'count' => $playerCount]));
            }

            $jerseyNumbers = array_column($players, 'jersey_number');
            // Убираем номера по умолчанию (0) из проверки на уникальность
            $uniqueNumbers = array_filter($jerseyNumbers, fn($number) => $number > 0);
            if (count($uniqueNumbers) !== count(array_unique($uniqueNumbers))) {
                $validator->errors()->add('players', __('api.roster_duplicate_jerseys'));
            }
        });
    }
}