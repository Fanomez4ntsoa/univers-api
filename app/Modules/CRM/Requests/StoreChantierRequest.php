<?php

namespace App\Modules\CRM\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChantierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id'          => ['required', 'integer', 'exists:clients,id'],
            'quote_id'           => ['nullable', 'integer', 'exists:quotes,id'],
            'address'            => ['required', 'string'],
            'city'               => ['nullable', 'string', 'max:100'],
            'postal_code'        => ['nullable', 'string', 'max:10'],
            'lat'                => ['nullable', 'numeric'],
            'lng'                => ['nullable', 'numeric'],
            'geofence_radius'    => ['nullable', 'integer', 'min:50', 'max:2000'],
            'chantier_type'      => ['nullable', 'string', 'in:renovation,construction,extension,plomberie,electricite,peinture,toiture,carrelage,maconnerie,autre'],
            'work_description'   => ['nullable', 'string'],
            'planned_start_date' => ['nullable', 'date'],
            'planned_end_date'   => ['nullable', 'date', 'after_or_equal:planned_start_date'],
            'assigned_workers'   => ['nullable', 'array'],
            'assigned_workers.*' => ['string'],
            'assigned_team'      => ['nullable', 'string', 'max:100'],
            'quote_amount'       => ['nullable', 'numeric', 'min:0'],
            'estimated_cost'     => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
