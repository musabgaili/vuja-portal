<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/** A correctly-shaped, self-explanatory import template with one example row. */
class StockTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'product_id', 'name', 'description',
            'purchase_price', 'price_student', 'price_entrepreneur', 'price_company',
            'unit', 'category',
            'malas_qty', 'noura_qty', 'monshaat_qty',
        ];
    }

    public function array(): array
    {
        return [
            ['SKU-1001', 'Arduino Uno R3', 'ATmega328P dev board',
                45.00, 55.00, 70.00, 85.00, 'piece', 'Electronics', 12, 8, 5],
        ];
    }
}
