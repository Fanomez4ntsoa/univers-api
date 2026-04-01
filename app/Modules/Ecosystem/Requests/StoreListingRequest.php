<?php

namespace App\Modules\Ecosystem\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price'       => ['required', 'numeric', 'min:0'],
            'price_type'  => ['nullable', 'string', 'in:fixed,negotiable,free'],
            'category'    => ['required', 'string', 'in:materiaux,outils,equipements,surplus_chantier,occasion'],
            'condition'   => ['required', 'string', 'in:new,used,refurbished'],
            'city'        => ['required', 'string', 'max:100'],
            'images'      => ['nullable', 'array'],
            'images.*'    => ['string'],
        ];
    }
}
