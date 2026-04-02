<?php

namespace App\Modules\Matching\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'              => ['sometimes', 'string', 'max:255'],
            'description'        => ['sometimes', 'string'],
            'category'           => ['sometimes', 'string', 'max:100'],
            'city'               => ['sometimes', 'string', 'max:100'],
            'budget_min'         => ['nullable', 'numeric', 'min:0'],
            'budget_max'         => ['nullable', 'numeric', 'min:0'],
            'urgency'            => ['nullable', 'string', 'in:normal,urgent,tres_urgent'],
            'images'             => ['nullable', 'array'],
            'images.*'           => ['string'],
            'desired_start_date' => ['nullable', 'date'],
        ];
    }
}
