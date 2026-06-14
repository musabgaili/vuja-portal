<?php

namespace App\Imports;

use App\Models\StaffTask;
use App\Models\User;
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
 * Bulk import of direct staff tasks. The assignee is resolved by assignee_email
 * (required); unknown emails are skipped + reported. Upserts on (assigned_to,
 * title). IP is NOT auto-awarded on import even if status=done.
 */
class StaffTaskImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    public int $created = 0;
    public int $updated = 0;
    public array $failures = [];

    public function collection(Collection $rows): void
    {
        $assignedBy = Auth::id();

        DB::transaction(function () use ($rows, $assignedBy) {
            foreach ($rows as $i => $row) {
                $title = trim((string) ($row['title'] ?? ''));
                if ($title === '') {
                    continue;
                }

                $assigneeId = User::where('email', trim((string) ($row['assignee_email'] ?? '')))->value('id');
                if (! $assigneeId) {
                    $this->failures[] = ['row' => $i + 2, 'column' => 'assignee_email', 'errors' => 'No user with this email'];

                    continue;
                }

                $category = in_array($row['category'] ?? '', StaffTask::CATEGORIES, true) ? $row['category'] : 'management';
                $priority = in_array($row['priority'] ?? '', StaffTask::PRIORITIES, true) ? $row['priority'] : 'normal';
                $status = in_array($row['status'] ?? '', StaffTask::STATUSES, true) ? $row['status'] : 'open';

                $existed = StaffTask::where('assigned_to', $assigneeId)->where('title', $title)->exists();
                StaffTask::updateOrCreate(
                    ['assigned_to' => $assigneeId, 'title' => $title],
                    [
                        'description' => $row['description'] ?? null,
                        'category' => $category,
                        'priority' => $priority,
                        'status' => $status,
                        'assigned_by' => $assignedBy,
                        'due_date' => $this->date($row['due_date'] ?? null),
                    ]
                );
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
            'title' => ['required', 'string', 'max:200'],
            'assignee_email' => ['required', 'email'],
        ];
    }

    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->failures[] = ['row' => $failure->row(), 'column' => $failure->attribute(), 'errors' => implode(', ', $failure->errors())];
        }
    }
}
