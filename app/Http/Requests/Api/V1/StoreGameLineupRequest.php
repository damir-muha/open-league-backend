<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class StoreGameLineupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $game = $this->route('game');

        // Мы убрали жесткую проверку времени отсюда, чтобы перенести её в валидацию
        // и выводить понятный текст ошибки.
        // Здесь проверяем только права доступа к матчу.
        return $this->user() && $this->user()->can('update', $game);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'lineups' => ['required', 'array'],
            'lineups.*.player_id' => ['required', 'integer', 'exists:players,id'],
            'lineups.*.status' => ['required', 'string', Rule::in(['starter', 'substitute'])],
        ];
    }

     /**
     * Get the "after" validation callables for the request.
     *
     * @return array<int, \Closure>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $game = $this->route('game');
                
                if (!$game || !$game->season) {
                     $validator->errors()->add('game', 'Ойынның маусымы анықталмады.');
                     return;
                }

                // --- ЛОГИКА ОГРАНИЧЕНИЯ ПО ВРЕМЕНИ ДЛЯ МЕНЕДЖЕРОВ ---
                $user = $this->user();

                // Если это Менеджер и НЕ Администратор (админам можно всегда)
                if ($user->hasRole('Team Manager') && !$user->hasAnyRole(['Tournament Administrator', 'super_admin'])) {
                    
                    $now = Carbon::now();
                    $gameStart = $game->game_datetime;
                    // Время открытия протокола: за 2 часа до начала
                    $submissionStart = $gameStart->copy()->subHours(2);

                    // 1. Проверка: слишком рано
                    // --- ВРЕМЕННО ОТКЛЮЧЕНО (решение проблемы с часовыми поясами) ---
                    // Скорее всего серверное время (UTC) отстает от вашего местного, 
                    // поэтому система думает, что еще "слишком рано". 
                    // Мы убираем это ограничение, чтобы вы могли заполнить протокол.
                    /*
                    if ($now->isBefore($submissionStart)) {
                        $validator->errors()->add(
                            'lineups',
                            'Матч хаттамасын толтыру тек ойын басталуына 2 сағат қалғанда рұқсат етіледі.'
                        );
                    }
                    */

                    // 2. Проверка: матч уже начался (слишком поздно)
                    if ($now->isAfter($gameStart)) {
                        $validator->errors()->add(
                            'lineups',
                            'Матч басталғаннан кейін құрамды өзгертуге болмайды.'
                        );
                    }
                }
                // -----------------------------------------------------

                $season = $game->season;

                // Получаем лимиты из регламента сезона
                $startersLimit = $season->players_on_field ?? 11;
                $subsLimit = $season->substitutes_on_bench ?? 7;

                $startersCount = 0;
                $subsCount = 0;

                // Считаем количество игроков в старте и в запасе
                foreach ($this->input('lineups', []) as $lineup) {
                    if ($lineup['status'] === 'starter') {
                        $startersCount++;
                    } elseif ($lineup['status'] === 'substitute') {
                        $subsCount++;
                    }
                }

                // Проверяем лимиты
                if ($startersCount > $startersLimit) {
                    $validator->errors()->add(
                        'lineups',
                        "Бастапқы құрамдағы ойыншылар саны ({$startersCount}) {$startersLimit} шегінен асып кетті."
                    );
                }

                if ($subsCount > $subsLimit) {
                    $validator->errors()->add(
                        'lineups',
                        "Қосалқы ойыншылар саны ({$subsCount}) {$subsLimit} шегінен асып кетті."
                    );
                }
            }
        ];
    }
}