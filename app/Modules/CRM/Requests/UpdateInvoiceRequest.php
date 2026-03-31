<?php

namespace App\Modules\CRM\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'                    => ['sometimes', 'array', 'min:1'],
            'items.*.description'      => ['required_with:items', 'string'],
            'items.*.details'          => ['nullable', 'string'],
            'items.*.quantity'         => ['required_with:items', 'numeric', 'min:0.01'],
            'items.*.unit'             => ['nullable', 'string', 'max:20'],
            'items.*.unit_price'       => ['required_with:items', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tva_rate'         => ['nullable', 'numeric', 'in:0,5.5,10,20'],
            'payment_terms'            => ['nullable', 'string', 'max:255'],
            'due_date'                 => ['nullable', 'date'],
            'notes'                    => ['nullable', 'string'],
        ];
    }
}
