<?php

namespace App\Imports;

use App\Models\Project;
use App\Models\ProjectTask;
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
 * Bulk import of project tasks into EXISTING projects. The project is resolved
 * by project_title (required; not created here) and the assignee by
 * assignee_email (optional). Upserts on (project_id, title).
 */
class ProjectTaskImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    public int $created = 0;
    public int $updated = 0;
    public array $failures = [];

    private const PRIORITIES = ['low', 'medium', 'high', 'urgent'];

    public function collection(Collection $rows): void
    {
        $createdBy = Auth::id();

        DB::transaction(function () use ($rows, $createdBy) {
            foreach ($rows as $i => $row) {
                $title = trim((string) ($row['title'] ?? ''));
                $projectTitle = trim((string) ($row['project_title'] ?? ''));
                if ($title === '' || $projectTitle === '') {
                    continue;
                }

                $projectId = Project::where('title', $projectTitle)->value('id');
                if (! $projectId) {
                    $this->failures[] = ['row' => $i + 2, 'column' => 'project_title', 'errors' => 'No project with this title'];

                    continue;
                }

                $assigneeId = ! empty($row['assignee_email'])
                    ? User::where('email', trim((string) $row['assignee_email']))->value('id')
                    : null;

                $priority = in_array($row['priority'] ?? '', self::PRIORITIES, true) ? $row['priority'] : 'medium';
                $status = trim((string) ($row['status'] ?? '')) ?: 'todo';

                $existed = ProjectTask::where('project_id', $projectId)->where('title', $title)->exists();
                ProjectTask::updateOrCreate(
                    ['project_id' => $projectId, 'title' => $title],
                    [
                        'description' => $row['description'] ?? null,
                        'priority' => $priority,
                        'status' => $status,
                        'assigned_to' => $assigneeId,
                        'created_by' => $createdBy,
                        'due_date' => $this->date($row['due_date'] ?? null),
                        'estimated_hours' => is_numeric($row['estimated_hours'] ?? null) ? (int) $row['estimated_hours'] : null,
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
            'title' => ['required', 'string', 'max:255'],
            'project_title' => ['required', 'string'],
        ];
    }

    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->failures[] = ['row' => $failure->row(), 'column' => $failure->attribute(), 'errors' => implode(', ', $failure->errors())];
        }
    }
}
