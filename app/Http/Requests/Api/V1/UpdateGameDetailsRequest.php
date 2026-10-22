<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGameDetailsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Проверяем, может ли пользователь обновлять информацию об игре
        return $this->user()->can('updateDetails', $this->route('game'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'venue_id' => ['nullable', 'integer', 'exists:venues,id'],
            'referee_ids' => ['nullable', 'array'],
            'referee_ids.*' => ['integer', 'exists:people,id'],
        ];
    }
}