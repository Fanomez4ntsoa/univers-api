<?php

namespace App\Modules\CRM\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddChantierDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'file_url'    => ['required', 'string', 'url'],
            'file_type'   => ['nullable', 'string', 'in:photo,plan,document,invoice'],
            'description' => ['nullable', 'string'],
        ];
    }
}
