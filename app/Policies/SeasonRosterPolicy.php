<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SeasonRoster;
use Illuminate\Auth\Access\HandlesAuthorization;

class SeasonRosterPolicy
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
        return $user->can('view_any_season::roster');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SeasonRoster $seasonRoster): bool
    {
        return $user->can('view_season::roster');
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
    public function update(User $user, SeasonRoster $seasonRoster): bool
    {
        if ($user->hasRole('Team Manager')) {
            return $user->person?->teams->contains($seasonRoster->team);
        }
        return $user->hasRole('Tournament Administrator');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SeasonRoster $seasonRoster): bool
    {
        return $user->can('delete_season::roster');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_season::roster');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, SeasonRoster $seasonRoster): bool
    {
        return $user->can('force_delete_season::roster');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_season::roster');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, SeasonRoster $seasonRoster): bool
    {
        return $user->can('restore_season::roster');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_season::roster');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, SeasonRoster $seasonRoster): bool
    {
        return $user->can('replicate_season::roster');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_season::roster');
    }
}
