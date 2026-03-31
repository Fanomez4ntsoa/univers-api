<?php

namespace App\Modules\CRM\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddChantierTimeEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'worker_name' => ['required', 'string', 'max:255'],
            'hours'       => ['required', 'numeric', 'min:0.01', 'max:24'],
            'date'        => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ];
    }
}
