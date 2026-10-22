<?php

namespace App\Observers;

use App\Models\Season;

class SeasonObserver
{
    /**
     * Handle the Season "saving" event.
     */
    public function saving(Season $season): void
    {
        // Если у сезона, который мы сохраняем, стоит флаг is_current = true...
        if ($season->is_current) {
            // ...то мы находим все остальные сезоны В ЭТОМ ЖЕ ТУРНИРЕ
            // и устанавливаем им is_current = false.
            Season::where('tournament_id', $season->tournament_id)
                  ->where('id', '!=', $season->id)
                  ->update(['is_current' => false]);
        }
    }
}