<?php

namespace App\Modules\Matching\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'              => ['required', 'string', 'max:255'],
            'description'        => ['required', 'string'],
            'category'           => ['required', 'string', 'max:100'],
            'city'               => ['required', 'string', 'max:100'],
            'budget_min'         => ['nullable', 'numeric', 'min:0'],
            'budget_max'         => ['nullable', 'numeric', 'min:0'],
            'urgency'            => ['nullable', 'string', 'in:normal,urgent,tres_urgent'],
            'images'             => ['nullable', 'array'],
            'images.*'           => ['string'],
            'desired_start_date' => ['nullable', 'date'],
        ];
    }
}
