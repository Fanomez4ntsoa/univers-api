<?php

namespace App\Modules\CRM\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddChantierCostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string'],
            'amount'      => ['required', 'numeric', 'min:0.01'],
            'category'    => ['nullable', 'string', 'in:materials,tools,transport,other'],
        ];
    }
}
