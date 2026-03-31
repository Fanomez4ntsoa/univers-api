<?php

namespace App\Modules\CRM\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChantierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address'            => ['sometimes', 'string'],
            'city'               => ['nullable', 'string', 'max:100'],
            'postal_code'        => ['nullable', 'string', 'max:10'],
            'lat'                => ['nullable', 'numeric'],
            'lng'                => ['nullable', 'numeric'],
            'geofence_radius'    => ['nullable', 'integer', 'min:50', 'max:2000'],
            'chantier_type'      => ['nullable', 'string', 'in:renovation,construction,extension,plomberie,electricite,peinture,toiture,carrelage,maconnerie,autre'],
            'work_description'   => ['nullable', 'string'],
            'planned_start_date' => ['nullable', 'date'],
            'planned_end_date'   => ['nullable', 'date'],
            'assigned_workers'   => ['nullable', 'array'],
            'assigned_workers.*' => ['string'],
            'assigned_team'      => ['nullable', 'string', 'max:100'],
            'estimated_cost'     => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
