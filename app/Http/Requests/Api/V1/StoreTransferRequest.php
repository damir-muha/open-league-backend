<?php
// --- Файл: app/Http/Requests/Api/V1/StoreTransferRequest.php ---

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Позже здесь будет проверка прав (Policy)
        return true;
    }

    public function rules(): array
    {
        return [
            'season_id' => ['required', 'exists:seasons,id'],
            'player_id' => ['required', 'exists:players,id'],
            'from_team_id' => ['required', 'exists:teams,id'],
            'to_team_id' => ['required', 'exists:teams,id', 'different:from_team_id'],
        ];
    }
}
