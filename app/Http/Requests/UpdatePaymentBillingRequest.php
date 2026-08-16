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

    public function attributes(): array
    {
        return [
            'tax_id' => __('portal.payments.tax_id'),
            'billing_address' => __('portal.payments.address'),
        ];
    }

    public function messages(): array
    {
        return [
            'tax_id.required' => __('portal.payments.validation.tax_id_required'),
            'tax_id.regex' => __('portal.payments.tax_id_invalid'),
            'billing_address.required' => __('portal.payments.validation.address_required'),
            'billing_address.min' => __('portal.payments.validation.address_min', ['min' => 10]),
            'billing_address.max' => __('portal.payments.validation.address_max', ['max' => 1000]),
        ];
    }
}
