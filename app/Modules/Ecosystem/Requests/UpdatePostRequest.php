<?php

namespace App\Modules\Ecosystem\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content'       => ['nullable', 'string'],
            'media_urls'    => ['nullable', 'array'],
            'media_urls.*'  => ['string'],
            'category'      => ['nullable', 'string', 'in:chantier,produit,conseil,avant_apres'],
            'visibility'    => ['nullable', 'string', 'in:public,private'],
        ];
    }
}
