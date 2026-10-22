<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Team;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability)
    {
        if ($user->hasRole('super_admin')) return true;
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Team $team): bool
    {
        return $user->can('view_team');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_team');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Team $team): bool
    {
        if ($user->hasRole('Team Manager')) {
            return $user->person?->teams->contains($team);
        }
        return $user->hasRole('Tournament Administrator');
    }
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Team $team): bool
    {
        return $user->can('delete_team');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_team');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Team $team): bool
    {
        return $user->can('force_delete_team');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_team');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Team $team): bool
    {
        return $user->can('restore_team');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_team');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Team $team): bool
    {
        return $user->can('replicate_team');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_team');
    }
}
