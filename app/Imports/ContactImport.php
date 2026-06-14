<?php

namespace App\Imports;

use App\Models\Company;
use App\Models\Contact;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

/**
 * Bulk import of CRM contacts. Upserts on email when present (otherwise creates).
 * A `company` column (name) is resolved to / creates a Company.
 */
class ContactImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
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

                $companyId = null;
                if (! empty($row['company'])) {
                    $companyId = Company::firstOrCreate(
                        ['name' => trim((string) $row['company'])],
                        ['owner_id' => $ownerId]
                    )->id;
                }

                $attrs = [
                    'name' => $name,
                    'job_title' => $row['job_title'] ?? null,
                    'phone' => $row['phone'] ?? null,
                    'company_id' => $companyId,
                    'is_primary' => filter_var($row['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'notes' => $row['notes'] ?? null,
                ];

                $email = trim((string) ($row['email'] ?? ''));
                if ($email !== '') {
                    $existed = Contact::where('email', $email)->exists();
                    Contact::updateOrCreate(['email' => $email], $attrs + ['owner_id' => $ownerId]);
                    $existed ? $this->updated++ : $this->created++;
                } else {
                    Contact::create($attrs + ['email' => null, 'owner_id' => $ownerId]);
                    $this->created++;
                }
            }
        });
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'max:40'],
            'job_title' => ['nullable', 'max:120'],
        ];
    }

    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->failures[] = ['row' => $failure->row(), 'column' => $failure->attribute(), 'errors' => implode(', ', $failure->errors())];
        }
    }
}
