<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlayerRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Проверяем, может ли пользователь редактировать игрока
        return $this->user()->can('update', $this->route('player'));
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'birth_date' => ['nullable', 'date'],
            'citizenship' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'in:Goalkeeper,Defender,Midfielder,Forward'],
            'status' => ['nullable', 'string', 'in:active,injured,suspended'],
        ];
    }
}