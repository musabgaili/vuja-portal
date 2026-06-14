<?php

namespace App\Exports;

use App\Models\StockItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Current inventory export. Columns mirror the import exactly and are keyed on
 * product_id, so the export → edit → re-import loop updates rows in place.
 */
class StockExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return StockItem::with('labs')->orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            'product_id', 'name', 'description',
            'purchase_price', 'price_student', 'price_entrepreneur', 'price_company',
            'unit', 'category',
            'malas_qty', 'noura_qty', 'monshaat_qty',
        ];
    }

    public function map($item): array
    {
        $qty = fn ($slug) => optional($item->labs->firstWhere('slug', $slug))->pivot->quantity ?? 0;

        return [
            $item->product_id, $item->name, $item->description,
            $item->purchase_price, $item->price_student, $item->price_entrepreneur, $item->price_company,
            $item->unit, $item->category,
            $qty('malas'), $qty('noura'), $qty('monshaat'),
        ];
    }
}
