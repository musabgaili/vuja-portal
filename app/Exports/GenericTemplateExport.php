<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * A reusable "download template" export: one header row + one example row.
 * Used by every entity import so users always get a correctly-shaped sheet.
 */
class GenericTemplateExport implements FromArray, WithHeadings
{
    public function __construct(private array $columns, private array $sample = [])
    {
    }

    public function headings(): array
    {
        return $this->columns;
    }

    public function array(): array
    {
        return $this->sample ? [$this->sample] : [];
    }
}
