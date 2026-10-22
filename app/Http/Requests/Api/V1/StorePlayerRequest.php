<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlayerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Player::class);
    }

    public function rules(): array
    {
        return [
            'first_name' => [
                'required', 
                'string', 
                'max:255',
                // ПРОВЕРКА НА ДУБЛИКАТ:
                // Нельзя создать игрока, если в базе уже есть такое Имя + Фамилия + Дата рождения
                Rule::unique('players')->where(function ($query) {
                    return $query->where('last_name', $this->last_name)
                                 ->where('birth_date', $this->birth_date);
                }),
            ],
            'last_name' => ['required', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'birth_date' => ['nullable', 'date'],
            'citizenship' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'in:Goalkeeper,Defender,Midfielder,Forward'],
            'status' => ['nullable', 'string', 'in:active,injured,suspended'],
        ];
    }

    public function messages()
    {
        return [
            'first_name.unique' => 'Игрок с таким именем, фамилией и датой рождения уже существует!',
        ];
    }
}