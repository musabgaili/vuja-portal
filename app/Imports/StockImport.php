<?php

namespace App\Imports;

use App\Models\Lab;
use App\Models\StockItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

/**
 * Bulk upsert of stock items keyed on product_id: an existing product_id is
 * updated, a new one is created. The {slug}_qty columns set stock per lab.
 */
class StockImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    public int $created = 0;
    public int $updated = 0;
    public array $failures = [];

    public function collection(Collection $rows): void
    {
        $labs = Lab::pluck('id', 'slug');   // slug => id

        DB::transaction(function () use ($rows, $labs) {
            foreach ($rows as $row) {
                $productId = trim((string) ($row['product_id'] ?? ''));
                if ($productId === '') {
                    continue;
                }

                $existed = StockItem::where('product_id', $productId)->exists();

                $item = StockItem::updateOrCreate(
                    ['product_id' => $productId],
                    [
                        'name' => $row['name'],
                        'description' => $row['description'] ?? null,
                        'purchase_price' => $row['purchase_price'] ?? 0,
                        'price_student' => $row['price_student'] ?? 0,
                        'price_entrepreneur' => $row['price_entrepreneur'] ?? 0,
                        'price_company' => $row['price_company'] ?? 0,
                        'unit' => $row['unit'] ?? null,
                        'category' => $row['category'] ?? null,
                    ]
                );

                $existed ? $this->updated++ : $this->created++;

                // Map {slug}_qty columns to pivot rows — works for any lab in the table.
                $pivot = [];
                foreach ($labs as $slug => $labId) {
                    $column = $slug.'_qty';
                    if ($row->has($column)) {
                        $pivot[$labId] = ['quantity' => (int) ($row[$column] ?? 0)];
                    }
                }
                if (! empty($pivot)) {
                    $item->labs()->sync($pivot);
                }
            }
        });
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required'],
            'name' => ['required', 'string', 'max:255'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'price_student' => ['nullable', 'numeric', 'min:0'],
            'price_entrepreneur' => ['nullable', 'numeric', 'min:0'],
            'price_company' => ['nullable', 'numeric', 'min:0'],
            'malas_qty' => ['nullable', 'integer', 'min:0'],
            'noura_qty' => ['nullable', 'integer', 'min:0'],
            'monshaat_qty' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->failures[] = [
                'row' => $failure->row(),
                'column' => $failure->attribute(),
                'errors' => implode(', ', $failure->errors()),
            ];
        }
    }
}
