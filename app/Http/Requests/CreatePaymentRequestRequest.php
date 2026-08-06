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
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:3000'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'amount' => ['required', 'decimal:0,2', 'min:1', 'max:99999999.99'],
            'send' => ['nullable', 'boolean'],
        ];
    }
}
