<?php

namespace App\Modules\Ecosystem\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'price'       => ['sometimes', 'numeric', 'min:0'],
            'price_type'  => ['nullable', 'string', 'in:fixed,negotiable,free'],
            'category'    => ['sometimes', 'string', 'in:materiaux,outils,equipements,surplus_chantier,occasion'],
            'condition'   => ['sometimes', 'string', 'in:new,used,refurbished'],
            'city'        => ['sometimes', 'string', 'max:100'],
            'images'      => ['nullable', 'array'],
            'images.*'    => ['string'],
        ];
    }
}
