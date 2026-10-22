<?php
// --- Файл: app/Http/Requests/Api/V1/UpdateTransferRequest.php ---

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['approved', 'rejected'])],
        ];
    }
}
