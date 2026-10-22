<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Game; 
use App\Observers\GameObserver; 
use App\Models\Season;
use App\Observers\SeasonObserver;
use App\Models\Event;
use App\Observers\EventObserver;
use App\Models\PointDeduction;
use App\Observers\PointDeductionObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Game::observe(GameObserver::class);
        Season::observe(SeasonObserver::class);
        Event::observe(EventObserver::class);
        PointDeduction::observe(PointDeductionObserver::class); 
        
    }
}
