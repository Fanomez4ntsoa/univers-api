<?php

namespace App\Modules\CRM\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'signature_image' => ['required', 'string'], // base64
            'signed_by'       => ['required', 'string', 'max:255'],
        ];
    }
}
