<?php

namespace App\Modules\Ecosystem\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['required', 'string'],
            'company_name'     => ['nullable', 'string', 'max:255'],
            'city'             => ['required', 'string', 'max:100'],
            'contract_type'    => ['required', 'string', 'in:cdi,cdd,interim,freelance,apprentissage'],
            'category'         => ['nullable', 'string', 'max:100'],
            'salary_min'       => ['nullable', 'numeric', 'min:0'],
            'salary_max'       => ['nullable', 'numeric', 'min:0'],
            'experience_level' => ['nullable', 'string', 'in:junior,intermediaire,senior'],
        ];
    }
}
