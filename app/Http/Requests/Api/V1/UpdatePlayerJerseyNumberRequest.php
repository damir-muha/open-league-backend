<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlayerJerseyNumberRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Проверяем, может ли пользователь редактировать эту заявку
        // Обратите внимание, что мы используем 'roster', а не 'seasonRoster' из-за именования в роутах
        return $this->user()->can('update', $this->route('roster'));
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $season = $this->route('roster')->season;
            $now = \Carbon\Carbon::now();
            $isBeforeDeadline = $now->lte($season->roster_deadline);
            $isInTransferWindow = $now->between($season->transfer_window_start, $season->transfer_window_end);

            if (!$isBeforeDeadline && !$isInTransferWindow) {
                $validator->errors()->add('jersey_number', 'Мерзім аяқталды. Нөмірді өзгертуге болмайды.');
            }
        });
    }

    public function rules(): array
    {
        $roster = $this->route('roster');
        $playerId = $this->route('player')->id;

        return [
            'jersey_number' => [
                'required',
                'integer',
                'min:1',
                'max:99',
                // Номер должен быть уникальным в пределах этой заявки,
                // за исключением самого себя (если мы не меняем номер).
                Rule::unique('player_season_roster')->where(function ($query) use ($roster) {
                    return $query->where('season_roster_id', $roster->id);
                })->ignore($playerId, 'player_id'),
            ],
        ];
    }
}