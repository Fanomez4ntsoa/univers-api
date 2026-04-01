<?php

namespace App\Modules\Ecosystem\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShopProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price'       => ['sometimes', 'numeric', 'min:0'],
            'images'      => ['nullable', 'array'],
            'images.*'    => ['string'],
            'category'    => ['nullable', 'string', 'max:100'],
            'stock'       => ['nullable', 'integer', 'min:-1'],
            'is_active'   => ['nullable', 'boolean'],
        ];
    }
}
