<?php

namespace App\Modules\Subscription\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan' => ['required', 'string', 'in:pro_monthly,pro_yearly'],
        ];
    }
}
