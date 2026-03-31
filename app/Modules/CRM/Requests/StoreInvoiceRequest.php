<?php

namespace App\Modules\CRM\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id'                => ['required', 'integer', 'exists:clients,id'],
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.description'      => ['required', 'string'],
            'items.*.details'          => ['nullable', 'string'],
            'items.*.quantity'         => ['required', 'numeric', 'min:0.01'],
            'items.*.unit'             => ['nullable', 'string', 'max:20'],
            'items.*.unit_price'       => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tva_rate'         => ['nullable', 'numeric', 'in:0,5.5,10,20'],
            'payment_terms'            => ['nullable', 'string', 'max:255'],
            'due_date'                 => ['nullable', 'date'],
            'notes'                    => ['nullable', 'string'],
        ];
    }
}
