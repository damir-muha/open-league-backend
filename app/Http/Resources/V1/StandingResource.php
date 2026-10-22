<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StandingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'team' => new TeamResource($this->whenLoaded('team')),
            'played' => $this->played,
            'wins' => $this->wins,
            'draws' => $this->draws,
            'losses' => $this->losses,
            'goals_for' => $this->goals_for,
            'goals_against' => $this->goals_against,
            'goal_difference' => $this->goal_difference,
            'points' => $this->points,
            'points_deducted' => $this->points_deducted,
            'final_points' => $this->final_points,
            'position' => $this->position,
            'home_played' => $this->home_played,
            'home_wins' => $this->home_wins,
            'home_draws' => $this->home_draws,
            'home_losses' => $this->home_losses,
            'home_goals_for' => $this->home_goals_for,
            'home_goals_against' => $this->home_goals_against,
            'home_points' => $this->home_points,
            'away_played' => $this->away_played,
            'away_wins' => $this->away_wins,
            'away_draws' => $this->away_draws,
            'away_losses' => $this->away_losses,
            'away_goals_for' => $this->away_goals_for,
            'away_goals_against' => $this->away_goals_against,
            'away_points' => $this->away_points,
            'recent_form' => $this->recent_form,
            // Добавляем название группы, если оно есть. Если нет - будет null.
            'group_name' => $this->whenLoaded('group', function () {
                // Эта проверка важна: если group_id был null, $this->group тоже будет null.
                return $this->group ? $this->group->name : null;
            })
        ];
    }
}