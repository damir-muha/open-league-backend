<?php

namespace App\Filament\Widgets;

use App\Models\Game;
use App\Models\Player;
use App\Models\Season;
use App\Models\Team;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Teams', Team::count())
                ->description('All registered teams')
                ->icon('heroicon-o-user-group'),
            Stat::make('Total Players', Player::count())
                ->description('All registered players')
                ->icon('heroicon-o-users'),
            Stat::make('Active Seasons', Season::where('is_current', true)->count())
                ->description('Currently active seasons')
                ->icon('heroicon-o-check-badge'),
            Stat::make('Scheduled Games', Game::where('status', 'scheduled')->count())
                ->description('Upcoming games')
                ->icon('heroicon-o-calendar-days'),
        ];
    }
}