<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isManager() === true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'string', 'email:filter', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'title_en' => ['required', 'string', 'max:180'],
            'title_ar' => ['required', 'string', 'max:180'],
            'description_en' => ['required', 'string', 'max:3000'],
            'description_ar' => ['required', 'string', 'max:3000'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'amount' => ['required', 'decimal:0,2', 'min:1', 'max:99999999.99'],
            'quote_number' => ['nullable', 'string', 'max:60'],
            'quote_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
            'send' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('portal.payments.name'),
            'email' => __('portal.payments.email'),
            'phone' => __('portal.payments.phone'),
            'title_en' => __('portal.payments.title_en'),
            'title_ar' => __('portal.payments.title_ar'),
            'description_en' => __('portal.payments.description_en'),
            'description_ar' => __('portal.payments.description_ar'),
            'quantity' => __('portal.payments.quantity'),
            'amount' => __('portal.payments.unit_amount'),
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('portal.payments.validation.email_required'),
            'email.email' => __('portal.payments.validation.email_invalid'),
            'email.max' => __('portal.payments.validation.email_max'),
        ];
    }
}
