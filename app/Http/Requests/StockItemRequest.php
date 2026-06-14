<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isManager() ?? false;
    }

    public function rules(): array
    {
        $itemId = $this->route('stockItem')?->id;

        return [
            'product_id' => ['required', 'string', 'max:100',
                Rule::unique('stock_items', 'product_id')->ignore($itemId)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'purchase_price' => ['required', 'numeric', 'min:0'],
            'price_student' => ['required', 'numeric', 'min:0'],
            'price_entrepreneur' => ['required', 'numeric', 'min:0'],
            'price_company' => ['required', 'numeric', 'min:0'],

            'unit' => ['nullable', 'string', 'max:50'],
            'category' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], // 2 MB

            'quantities' => ['array'],            // quantities[lab_id] => qty
            'quantities.*' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
