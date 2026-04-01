<?php

namespace App\Modules\Ecosystem\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'         => ['sometimes', 'string', 'max:255'],
            'description'   => ['sometimes', 'string'],
            'event_type'    => ['sometimes', 'string', 'in:formation,salon,conference,reunion,autre'],
            'city'          => ['sometimes', 'string', 'max:100'],
            'address'       => ['nullable', 'string'],
            'start_date'    => ['sometimes', 'date'],
            'end_date'      => ['nullable', 'date'],
            'max_attendees' => ['nullable', 'integer', 'min:1'],
            'is_free'       => ['nullable', 'boolean'],
            'price'         => ['nullable', 'numeric', 'min:0'],
            'image_url'     => ['nullable', 'string'],
            'status'        => ['sometimes', 'string', 'in:upcoming,ongoing,completed,cancelled'],
        ];
    }
}
