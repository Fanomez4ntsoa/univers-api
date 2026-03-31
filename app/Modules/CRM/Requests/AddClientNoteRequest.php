<?php

namespace App\Modules\CRM\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddClientNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content'       => ['required', 'string'],
            'is_voice'      => ['sometimes', 'boolean'],
            'voice_url'     => ['nullable', 'string', 'url'],
            'transcription' => ['nullable', 'string'],
        ];
    }
}
