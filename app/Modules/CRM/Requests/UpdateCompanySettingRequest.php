<?php

namespace App\Modules\CRM\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanySettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name'  => ['nullable', 'string', 'max:255'],
            'logo_url'      => ['nullable', 'string', 'url'],
            'address'       => ['nullable', 'string', 'max:500'],
            'city'          => ['nullable', 'string', 'max:100'],
            'postal_code'   => ['nullable', 'string', 'max:10'],
            'phone'         => ['nullable', 'string', 'max:30'],
            'email'         => ['nullable', 'email'],
            'website'       => ['nullable', 'string', 'url'],
            'siret'         => ['nullable', 'string', 'max:20'],
            'tva_number'    => ['nullable', 'string', 'max:30'],
            'cgv_text'      => ['nullable', 'string'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'bank_details'  => ['nullable', 'array'],
        ];
    }
}
