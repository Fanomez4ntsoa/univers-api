<?php

namespace App\Modules\CRM\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProspectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:255'],
            'phone'              => ['nullable', 'string', 'max:30'],
            'email'              => ['nullable', 'email'],
            'address'            => ['nullable', 'string'],
            'city'               => ['nullable', 'string', 'max:100'],
            'postal_code'        => ['nullable', 'string', 'max:10'],
            'chantier_type'      => ['nullable', 'string', 'in:renovation,construction,extension,plomberie,electricite,peinture,toiture,carrelage,maconnerie,autre'],
            'estimated_value'    => ['nullable', 'numeric', 'min:0'],
            'source'             => ['nullable', 'string', 'max:100'],
            'notes'              => ['nullable', 'string'],
            'next_followup_date' => ['nullable', 'date'],
        ];
    }
}
