<?php

namespace App\Imports;

use App\Models\Company;
use App\Models\Opportunity;
use App\Models\PipelineStage;
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
 * Bulk import of sales opportunities. Upserts on email when present, else on
 * name. Unknown/blank stage falls back to the first active pipeline stage.
 */
class OpportunityImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    public int $created = 0;
    public int $updated = 0;
    public array $failures = [];

    public function collection(Collection $rows): void
    {
        $ownerId = Auth::id();
        $stageKeys = PipelineStage::keys();
        $defaultStage = $stageKeys[0] ?? 'new';

        DB::transaction(function () use ($rows, $ownerId, $stageKeys, $defaultStage) {
            foreach ($rows as $row) {
                $name = trim((string) ($row['name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $stage = trim((string) ($row['stage'] ?? ''));
                if (! in_array($stage, $stageKeys, true)) {
                    $stage = $defaultStage;
                }

                $companyId = null;
                if (! empty($row['company_name'])) {
                    $companyId = Company::firstOrCreate(
                        ['name' => trim((string) $row['company_name'])],
                        ['owner_id' => $ownerId]
                    )->id;
                }

                $attrs = [
                    'name' => $name,
                    'company_name' => $row['company_name'] ?? null,
                    'contact_name' => $row['contact_name'] ?? null,
                    'phone' => $row['phone'] ?? null,
                    'source' => $row['source'] ?? null,
                    'stage' => $stage,
                    'expected_value' => is_numeric($row['expected_value'] ?? null) ? $row['expected_value'] : 0,
                    'probability' => is_numeric($row['probability'] ?? null) ? (int) $row['probability'] : 0,
                    'expected_close_date' => $this->date($row['expected_close_date'] ?? null),
                    'description' => $row['description'] ?? null,
                    'company_id' => $companyId,
                    'owner_id' => $ownerId,
                ];

                $email = trim((string) ($row['email'] ?? ''));
                $key = $email !== '' ? ['email' => $email] : ['name' => $name];
                $existed = Opportunity::where($key)->exists();
                Opportunity::updateOrCreate($key, $attrs + ['email' => $email ?: null]);
                $existed ? $this->updated++ : $this->created++;
            }
        });
    }

    private function date($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        try {
            return \Carbon\Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'email' => ['nullable', 'email', 'max:160'],
            'expected_value' => ['nullable', 'numeric', 'min:0'],
            'probability' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->failures[] = ['row' => $failure->row(), 'column' => $failure->attribute(), 'errors' => implode(', ', $failure->errors())];
        }
    }
}
