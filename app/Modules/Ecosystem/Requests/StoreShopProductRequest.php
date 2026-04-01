<?php

namespace App\Modules\Ecosystem\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShopProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price'       => ['required', 'numeric', 'min:0'],
            'images'      => ['nullable', 'array'],
            'images.*'    => ['string'],
            'category'    => ['nullable', 'string', 'max:100'],
            'stock'       => ['nullable', 'integer', 'min:-1'],
            'is_active'   => ['nullable', 'boolean'],
        ];
    }
}
