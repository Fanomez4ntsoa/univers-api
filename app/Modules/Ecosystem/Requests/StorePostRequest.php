<?php

namespace App\Modules\Ecosystem\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
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
            'post_type'     => ['nullable', 'string', 'in:text,photo,carousel,reel,video'],
            'category'      => ['nullable', 'string', 'in:chantier,produit,conseil,avant_apres'],
            'visibility'    => ['nullable', 'string', 'in:public,private'],
        ];
    }
}
