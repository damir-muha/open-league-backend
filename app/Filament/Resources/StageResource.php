<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StageResource\Pages;
use App\Filament\Resources\StageResource\RelationManagers;
use App\Models\Stage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class StageResource extends Resource
{
    protected static ?string $model = Stage::class;
    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('season_id')
                    ->relationship('season', 'name')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options([
                        'league' => 'League / Regular Season',
                        'group' => 'Group Stage',
                        'knockout' => 'Knockout / Playoffs',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('order')
                    ->required()
                    ->numeric(),
                Forms\Components\Toggle::make('is_default')
                    ->label('Is Default')
                    ->helperText('Select this stage by default on page load'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('season.name')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('order')->numeric()->sortable(),
                Tables\Columns\IconColumn::make('is_default')
                    ->boolean()
                    ->label('Default'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RoundRelationManager::class,
            RelationManagers\GroupRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStages::route('/'),
            'create' => Pages\CreateStage::route('/create'),
            'edit' => Pages\EditStage::route('/{record}/edit'),
        ];
    }

    public static function handleScheduleGeneration(array $data, Stage $stage): void
    {
        $stage->rounds()->each(fn ($round) => $round->games()->delete());
        $stage->rounds()->delete();

        $season = $stage->season;
        $gameDays = array_map('intval', $data['days_of_week']);
        $currentDate = Carbon::parse($season->start_date);

        if ($stage->type === 'league') {
            $teams = $season->teams()->pluck('id')->toArray();
            if (count($teams) < 2) {
                Notification::make()->title('Not enough teams in the season')->warning()->send();
                return;
            }
            $schedule = self::generateRoundRobinSchedule($teams, $data['circles']);
            self::createGamesFromSchedule($schedule, $stage, $currentDate, $gameDays);
        }

        if ($stage->type === 'group') {
            $groups = $stage->groups()->with('teams')->get();
            if ($groups->isEmpty()) {
                Notification::make()->title('No groups in the stage')->warning()->send();
                return;
            }
            foreach ($groups as $group) {
                $teams = $group->teams()->pluck('id')->toArray();
                if (count($teams) < 2) continue;
                $schedule = self::generateRoundRobinSchedule($teams, $data['circles']);
                self::createGamesFromSchedule($schedule, $stage, $currentDate, $gameDays, $group->id);
            }
        }

        Notification::make()->title('Schedule generated successfully!')->success()->send();
    }

    private static function createGamesFromSchedule(array $schedule, Stage $stage, Carbon $startDate, array $gameDays, ?int $groupId = null): void
    {
        $currentDate = $startDate->copy();
        foreach ($schedule as $roundNumber => $pairings) {
            $round = $stage->rounds()->create(['name' => "Round {$roundNumber}"]);
            while (!in_array($currentDate->dayOfWeek, $gameDays)) {
                $currentDate->addDay();
            }
            foreach ($pairings as $pair) {
                $round->games()->create([
                    'season_id' => $stage->season_id,
                    'stage_id' => $stage->id,
                    'group_id' => $groupId,
                    'home_team_id' => $pair[0],
                    'away_team_id' => $pair[1],
                    'game_datetime' => $currentDate->copy()->setTime(0, 0),
                ]);
            }
            $currentDate->addDay();
        }
    }
    
    // --- НАЧАЛО ИСПРАВЛЕННОЙ ЛОГИКИ ---
    private static function generateRoundRobinSchedule(array $teams, int $circles = 1): array
    {
        if (count($teams) % 2 !== 0) {
            $teams[] = null; // Добавляем "пустышку" для нечетного числа команд
        }
        $teamCount = count($teams);
        $roundCount = $teamCount - 1;
        
        $firstCircleSchedule = [];

        // 1. Генерируем полный первый круг по "круговому" алгоритму
        for ($r = 0; $r < $roundCount; $r++) {
            $roundPairings = [];
            for ($i = 0; $i < $teamCount / 2; $i++) {
                $home = $teams[$i];
                $away = $teams[$teamCount - 1 - $i];

                if ($home !== null && $away !== null) {
                    // Чередуем хозяев/гостей в каждом туре для разнообразия
                    $roundPairings[] = ($r % 2 === 0) ? [$home, $away] : [$away, $home];
                }
            }
            $firstCircleSchedule[$r + 1] = $roundPairings;
            
            // Вращаем массив команд: последний элемент становится вторым
            $lastTeam = array_pop($teams);
            array_splice($teams, 1, 0, [$lastTeam]);
        }
        
        // 2. Генерируем последующие круги на основе первого
        $fullSchedule = $firstCircleSchedule;
        for ($c = 2; $c <= $circles; $c++) {
            foreach ($firstCircleSchedule as $roundNumber => $pairings) {
                $newRoundNumber = $roundNumber + ($roundCount * ($c - 1));
                $newPairings = [];
                foreach ($pairings as $pair) {
                    // Для четных кругов (2, 4...) меняем хозяев и гостей.
                    // Для нечетных (3, 5...) оставляем как в первом круге.
                    $newPairings[] = ($c % 2 === 0) ? [$pair[1], $pair[0]] : [$pair[0], $pair[1]];
                }
                $fullSchedule[$newRoundNumber] = $newPairings;
            }
        }
        
        ksort($fullSchedule);
        return $fullSchedule;
    }
    // --- КОНЕЦ ИСПРАВЛЕННОЙ ЛОГИКИ ---
}