<?php

namespace App\Modules\CRM\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id'               => ['required', 'integer', 'exists:clients,id'],
            'title'                   => ['required', 'string', 'max:255'],
            'chantier_type'           => ['nullable', 'string', 'in:renovation,construction,extension,plomberie,electricite,peinture,toiture,carrelage,maconnerie,autre'],
            'chantier_address'        => ['nullable', 'string'],
            'work_description'        => ['nullable', 'string'],
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.description'     => ['required', 'string'],
            'items.*.details'         => ['nullable', 'string'],
            'items.*.quantity'        => ['required', 'numeric', 'min:0.01'],
            'items.*.unit'            => ['nullable', 'string', 'max:20'],
            'items.*.unit_price'      => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tva_rate'        => ['nullable', 'numeric', 'in:0,5.5,10,20'],
            'global_discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'validity_days'           => ['nullable', 'integer', 'min:1', 'max:365'],
            'payment_terms'           => ['nullable', 'string', 'max:255'],
            'payment_delay_days'      => ['nullable', 'integer', 'min:0'],
            'notes'                   => ['nullable', 'string'],
            'internal_notes'          => ['nullable', 'string'],
            'terms_and_conditions'    => ['nullable', 'string'],
        ];
    }
}
