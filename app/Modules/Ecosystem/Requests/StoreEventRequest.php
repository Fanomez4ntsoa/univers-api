<?php

namespace App\Modules\Ecosystem\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['required', 'string'],
            'event_type'    => ['required', 'string', 'in:formation,salon,conference,reunion,autre'],
            'city'          => ['required', 'string', 'max:100'],
            'address'       => ['nullable', 'string'],
            'start_date'    => ['required', 'date'],
            'end_date'      => ['nullable', 'date', 'after_or_equal:start_date'],
            'max_attendees' => ['nullable', 'integer', 'min:1'],
            'is_free'       => ['nullable', 'boolean'],
            'price'         => ['nullable', 'numeric', 'min:0'],
            'image_url'     => ['nullable', 'string'],
        ];
    }
}
