<?php

namespace App\Modules\Ecosystem\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShopRequest extends FormRequest
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
            'logo_url'    => ['nullable', 'string'],
            'cover_url'   => ['nullable', 'string'],
            'category'    => ['nullable', 'string', 'max:100'],
            'city'        => ['nullable', 'string', 'max:100'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }
}
