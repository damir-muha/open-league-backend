<?php
namespace App\Observers;
use App\Models\PointDeduction;
use App\Models\Standing;

class PointDeductionObserver
{
    // Срабатывает ПОСЛЕ создания или обновления штрафа
    public function saved(PointDeduction $deduction) {
        $this->recalculate($deduction->season_id, $deduction->team_id);
    }
    
    // Срабатывает ПОСЛЕ удаления штрафа
    public function deleted(PointDeduction $deduction) {
        $this->recalculate($deduction->season_id, $deduction->team_id);
    }
    
    protected function recalculate(int $seasonId, int $teamId)
    {
        // Суммируем ВСЕ штрафы для этой команды в этом сезоне
        $totalDeducted = PointDeduction::where('season_id', $seasonId)
            ->where('team_id', $teamId)
            ->sum('points');

        // Находим или создаем запись в турнирной таблице
        $standing = Standing::firstOrCreate(
            ['season_id' => $seasonId, 'team_id' => $teamId]
        );

        // Обновляем только поле со штрафами
        $standing->points_deducted = $totalDeducted;
        $standing->save();
    }
}