<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGameEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Проверяем, может ли пользователь (судья) обновлять информацию об игре
        return $this->user()->can('update', $this->route('game'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'team_id' => ['required', 'integer', 'exists:teams,id'],
            'player_id' => ['required', 'integer', 'exists:players,id'],
            'type' => ['required', 'string', Rule::in(['goal', 'yellow_card', 'red_card', 'second_yellow_card', 'assist', 'substitution_in', 'substitution_out'])],
            'minute' => ['required', 'integer', 'min:1'],
            'related_player_id' => ['nullable', 'integer', 'exists:players,id', 'different:player_id'],
            'suspension_duration' => ['nullable', 'integer', 'min:1'],
        ];
    }
}