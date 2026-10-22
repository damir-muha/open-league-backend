<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Suspension;
use Illuminate\Auth\Access\HandlesAuthorization;

class SuspensionPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability)
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
    }

    // Кто может видеть список (Админы, Судьи, Менеджеры)
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Tournament Administrator', 'Referee', 'Team Manager']);
    }

    // Кто может удалять/снимать дисквалификацию (Только Админ)
    public function delete(User $user, Suspension $suspension): bool
    {
        return $user->hasRole('Tournament Administrator');
    }
}