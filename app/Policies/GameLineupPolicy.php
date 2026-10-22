<?php

namespace App\Policies;

use App\Models\User;
use App\Models\GameLineup;
use Illuminate\Auth\Access\HandlesAuthorization;

class GameLineupPolicy
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
        return $user->can('view_any_game::lineup');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GameLineup $gameLineup): bool
    {
        return $user->can('view_game::lineup');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Team Manager', 'Tournament Administrator']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GameLineup $gameLineup): bool
    {
        if ($user->hasRole('Team Manager')) {
            // Менеджер может редактировать состав, если его команда участвует в этом матче
            $isParticipant = $gameLineup->game->home_team_id == $gameLineup->team_id || $gameLineup->game->away_team_id == $gameLineup->team_id;
            return $user->person?->teams->contains($gameLineup->team_id) && $isParticipant;
        }
        return $user->hasRole('Tournament Administrator');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GameLineup $gameLineup): bool
    {
        return $user->can('delete_game::lineup');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_game::lineup');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, GameLineup $gameLineup): bool
    {
        return $user->can('force_delete_game::lineup');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_game::lineup');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, GameLineup $gameLineup): bool
    {
        return $user->can('restore_game::lineup');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_game::lineup');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, GameLineup $gameLineup): bool
    {
        return $user->can('replicate_game::lineup');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_game::lineup');
    }
}
