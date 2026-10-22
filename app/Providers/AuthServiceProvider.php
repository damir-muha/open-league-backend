<?php

namespace App\Providers;

use App\Models\Game;
use App\Models\GameLineup;
use App\Models\Player; 
use App\Models\Team;
use App\Models\TransferRequest;
use App\Models\Suspension;
use App\Policies\GameLineupPolicy;
use App\Policies\GamePolicy;
use App\Policies\NewsPolicy;
use App\Policies\PlayerPolicy; 
use App\Policies\TeamPolicy;
use App\Policies\TransferRequestPolicy;
use App\Policies\SuspensionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Team::class => TeamPolicy::class,
        SeasonRoster::class => SeasonRosterPolicy::class,
        Game::class => GamePolicy::class,
        TransferRequest::class => TransferRequestPolicy::class,
        GameLineup::class => GameLineupPolicy::class,
		Player::class => PlayerPolicy::class, 
        Suspension::class => SuspensionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}