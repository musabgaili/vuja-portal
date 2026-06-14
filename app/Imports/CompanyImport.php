<?php

namespace App\Imports;

use App\Models\Company;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

/** Bulk import of companies. Upserts on the (required) company name. */
class CompanyImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    public int $created = 0;
    public int $updated = 0;
    public array $failures = [];

    public function collection(Collection $rows): void
    {
        $ownerId = Auth::id();

        DB::transaction(function () use ($rows, $ownerId) {
            foreach ($rows as $row) {
                $name = trim((string) ($row['name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $existed = Company::where('name', $name)->exists();
                Company::updateOrCreate(['name' => $name], [
                    'industry' => $row['industry'] ?? null,
                    'website' => $row['website'] ?? null,
                    'email' => $row['email'] ?? null,
                    'phone' => $row['phone'] ?? null,
                    'address' => $row['address'] ?? null,
                    'notes' => $row['notes'] ?? null,
                    'owner_id' => $ownerId,
                ]);
                $existed ? $this->updated++ : $this->created++;
            }
        });
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'email' => ['nullable', 'email', 'max:160'],
            'website' => ['nullable', 'max:200'],
        ];
    }

    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->failures[] = ['row' => $failure->row(), 'column' => $failure->attribute(), 'errors' => implode(', ', $failure->errors())];
        }
    }
}
