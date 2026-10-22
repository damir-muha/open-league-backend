<?php
// --- Файл: app/Http/Resources/V1/SeasonResource.php ---

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon; 

class SeasonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
		$now = Carbon::now();
        $isRosterOpen = $now->isBefore($this->roster_deadline);
        $isTransferWindowOpen = $now->between($this->transfer_window_start, $this->transfer_window_end);
		
        return [
            'id' => $this->id,
            'name' => $this->name,
            'startDate' => $this->start_date,
            'endDate' => $this->end_date,
            'isCurrent' => $this->is_current,
            'standingsDisplayMode' => $this->standings_display_mode,
            'format' => $this->format_type,
            'tournament' => new TournamentResource($this->whenLoaded('tournament')),
			
			'rosterDeadline' => $this->roster_deadline,
            'isRosterOpen' => $isRosterOpen, 

            'transferWindowStart' => $this->transfer_window_start,
            'transferWindowEnd' => $this->transfer_window_end,
            'isTransferWindowOpen' => $isTransferWindowOpen,
			
			'playersOnField' => $this->players_on_field,
            'substitutesOnBench' => $this->substitutes_on_bench,
            
            'stages' => $this->whenLoaded('stages', function () {
                return $this->stages->map(function ($stage) {
                    return [
                        'id' => $stage->id,
                        'name' => $stage->name,
                        'type' => $stage->type,
                        'is_default' => (bool) $stage->is_default,
                        'rounds' => $stage->rounds->map(function ($round) {
                            return [
                                'id' => $round->id,
                                'name' => $round->name,
                            ];
                        }),
                    ];
                });
            }),
        ];
    }
}