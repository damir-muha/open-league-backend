<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Game;
use Illuminate\Auth\Access\HandlesAuthorization;

class GamePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function before(User $user, string $ability)
    {
        if ($user->hasRole('super_admin')) return true;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_game');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Game $game): bool
    {
        return $user->can('view_game');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_game');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Game $game): bool
    {
        if ($user->hasRole('Referee')) {
            return $game->referees->contains('id', $user->person?->id);
        }
        
        if ($user->hasRole('Tournament Administrator')) {
            return true;
        }
		
		if ($user->hasRole('Team Manager') && $user->person) {
            $personTeams = $user->person->teams->pluck('id');
            return $personTeams->contains($game->home_team_id) || $personTeams->contains($game->away_team_id);
        }

        return false;
    }

    /**
     * Determine whether the user can update the details (venue, referees) of the model.
     */
    public function updateDetails(User $user, Game $game): bool
    {
        return $user->hasRole('Tournament Administrator') || $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Game $game): bool
    {
        return $user->can('delete_game');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_game');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Game $game): bool
    {
        return $user->can('force_delete_game');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_game');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Game $game): bool
    {
        return $user->can('restore_game');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_game');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Game $game): bool
    {
        return $user->can('replicate_game');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_game');
    }
}
