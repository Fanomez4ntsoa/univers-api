<?php

namespace App\Modules\ClientPortal\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignQuotePortalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'signature_image' => ['required', 'string'], // base64 or URL
            'signed_by'       => ['required', 'string', 'max:255'],
        ];
    }
}
