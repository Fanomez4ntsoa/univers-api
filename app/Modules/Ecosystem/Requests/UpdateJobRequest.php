<?php

namespace App\Modules\Ecosystem\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'            => ['sometimes', 'string', 'max:255'],
            'description'      => ['sometimes', 'string'],
            'company_name'     => ['nullable', 'string', 'max:255'],
            'city'             => ['sometimes', 'string', 'max:100'],
            'contract_type'    => ['sometimes', 'string', 'in:cdi,cdd,interim,freelance,apprentissage'],
            'category'         => ['nullable', 'string', 'max:100'],
            'salary_min'       => ['nullable', 'numeric', 'min:0'],
            'salary_max'       => ['nullable', 'numeric', 'min:0'],
            'experience_level' => ['nullable', 'string', 'in:junior,intermediaire,senior'],
            'is_active'        => ['sometimes', 'boolean'],
        ];
    }
}
