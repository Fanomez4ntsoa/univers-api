<?php

namespace App\Modules\Matching\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message'    => ['nullable', 'string'],
            'price'      => ['required', 'numeric', 'min:0'],
            'delay_days' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
