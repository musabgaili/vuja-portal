<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentBillingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tax_id' => ['required', 'regex:/^3[0-9]{13}3$/'],
            'billing_address' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'tax_id.regex' => __('portal.payments.tax_id_invalid'),
        ];
    }
}
