<?php

namespace App\Imports;

use App\Models\Project;
use App\Models\ProjectPerson;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

/**
 * Bulk import of projects. The client is resolved by client_email (required);
 * rows whose client email is unknown are skipped and reported. Upserts on
 * (title, client_id) and keeps ProjectPerson client/PM rows in sync.
 */
class ProjectImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    public int $created = 0;
    public int $updated = 0;
    public array $failures = [];

    private const STATUSES = ['planning', 'quoted', 'awarded', 'in_progress', 'paused', 'completed', 'lost', 'cancelled'];

    public function collection(Collection $rows): void
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $i => $row) {
                $title = trim((string) ($row['title'] ?? ''));
                if ($title === '') {
                    continue;
                }

                $clientId = User::where('email', trim((string) ($row['client_email'] ?? '')))->value('id');
                if (! $clientId) {
                    $this->failures[] = ['row' => $i + 2, 'column' => 'client_email', 'errors' => 'No user with this email'];

                    continue;
                }

                $pmId = ! empty($row['project_manager_email'])
                    ? User::where('email', trim((string) $row['project_manager_email']))->value('id')
                    : null;

                $status = in_array($row['status'] ?? '', self::STATUSES, true) ? $row['status'] : 'planning';

                $existed = Project::where('title', $title)->where('client_id', $clientId)->exists();
                $project = Project::updateOrCreate(
                    ['title' => $title, 'client_id' => $clientId],
                    [
                        'description' => $row['description'] ?: ('Imported: '.$title),
                        'scope' => $row['scope'] ?? null,
                        'status' => $status,
                        'budget' => is_numeric($row['budget'] ?? null) ? $row['budget'] : null,
                        'start_date' => $this->date($row['start_date'] ?? null),
                        'end_date' => $this->date($row['end_date'] ?? null),
                        'project_manager_id' => $pmId,
                    ]
                );

                // Keep access rows in sync.
                ProjectPerson::firstOrCreate(['project_id' => $project->id, 'user_id' => $clientId, 'role' => 'client'], ['can_edit' => false]);
                if ($pmId) {
                    ProjectPerson::firstOrCreate(['project_id' => $project->id, 'user_id' => $pmId, 'role' => 'project_manager'], ['can_edit' => true]);
                }

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
            'title' => ['required', 'string', 'max:255'],
            'client_email' => ['required', 'email'],
            'budget' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->failures[] = ['row' => $failure->row(), 'column' => $failure->attribute(), 'errors' => implode(', ', $failure->errors())];
        }
    }
}
