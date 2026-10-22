<?php
// --- Файл: app/Http/Requests/Api/V1/UpdateGameScoreRequest.php ---

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGameScoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Позже здесь будет проверка, что пользователь - судья этого матча
        return true;
    }

    public function rules(): array
    {
        return [
            'home_score' => ['required', 'integer', 'min:0'],
            'away_score' => ['required', 'integer', 'min:0'],
            'home_penalty_score' => ['nullable', 'integer', 'min:0'],
            'away_penalty_score' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', Rule::in(['finished', 'postponed'])],
        ];
    }
}