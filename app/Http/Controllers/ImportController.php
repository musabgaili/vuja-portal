<?php

namespace App\Http\Controllers;

use App\Exports\GenericTemplateExport;
use App\Imports\CompanyImport;
use App\Imports\ContactImport;
use App\Imports\OpportunityImport;
use App\Imports\ProjectImport;
use App\Imports\ProjectTaskImport;
use App\Imports\StaffTaskImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

/**
 * One controller for every Excel/CSV bulk import. Each entity is described once
 * in the ENTITIES map (import class, columns, example row, index route). All
 * imports upsert (create new + update existing) and report skipped rows.
 */
class ImportController extends Controller
{
    private function entities(): array
    {
        return [
            'contacts' => [
                'import' => ContactImport::class,
                'title' => __('portal.import.contacts'),
                'index' => 'contacts.index',
                'columns' => ['name', 'email', 'phone', 'job_title', 'company', 'is_primary', 'notes'],
                'sample' => ['Jane Doe', 'jane@acme.com', '+966500000000', 'CTO', 'Acme Corp', '1', 'Met at expo'],
                'note' => __('portal.import.note_contacts'),
            ],
            'companies' => [
                'import' => CompanyImport::class,
                'title' => __('portal.import.companies'),
                'index' => 'companies.index',
                'columns' => ['name', 'industry', 'website', 'email', 'phone', 'address', 'notes'],
                'sample' => ['Acme Corp', 'Manufacturing', 'https://acme.com', 'info@acme.com', '+966500000000', 'Riyadh', ''],
                'note' => __('portal.import.note_companies'),
            ],
            'opportunities' => [
                'import' => OpportunityImport::class,
                'title' => __('portal.import.opportunities'),
                'index' => 'crm.index',
                'columns' => ['name', 'company_name', 'contact_name', 'email', 'phone', 'source', 'stage', 'expected_value', 'probability', 'expected_close_date', 'description'],
                'sample' => ['Website revamp', 'Acme Corp', 'Jane Doe', 'jane@acme.com', '+966500000000', 'Referral', 'new', '50000', '40', '2026-09-01', ''],
                'note' => __('portal.import.note_opportunities'),
            ],
            'projects' => [
                'import' => ProjectImport::class,
                'title' => __('portal.import.projects'),
                'index' => 'projects.manager.index',
                'columns' => ['title', 'client_email', 'description', 'scope', 'status', 'budget', 'start_date', 'end_date', 'project_manager_email'],
                'sample' => ['Brand refresh', 'client@acme.com', 'Full rebrand', '8 page templates', 'in_progress', '120000', '2026-07-01', '2026-10-01', 'pm@vujade.com'],
                'note' => __('portal.import.note_projects'),
            ],
            'staff-tasks' => [
                'import' => StaffTaskImport::class,
                'title' => __('portal.import.staff_tasks'),
                'index' => 'staff-tasks.index',
                'columns' => ['title', 'assignee_email', 'category', 'priority', 'status', 'due_date', 'description'],
                'sample' => ['Prepare Q3 deck', 'employee@vujade.com', 'sales', 'high', 'open', '2026-08-01', ''],
                'note' => __('portal.import.note_staff_tasks'),
            ],
            'project-tasks' => [
                'import' => ProjectTaskImport::class,
                'title' => __('portal.import.project_tasks'),
                'index' => 'projects.manager.index',
                'columns' => ['project_title', 'title', 'assignee_email', 'priority', 'status', 'due_date', 'estimated_hours', 'description'],
                'sample' => ['Brand refresh', 'Design homepage', 'employee@vujade.com', 'high', 'todo', '2026-08-15', '12', ''],
                'note' => __('portal.import.note_project_tasks'),
            ],
        ];
    }

    private function config(string $entity): array
    {
        return $this->entities()[$entity] ?? abort(404);
    }

    public function form(string $entity)
    {
        abort_unless(Auth::user()?->isManager(), 403);
        $cfg = $this->config($entity);

        return view('imports.form', [
            'title' => $cfg['title'],
            'columns' => $cfg['columns'],
            'note' => $cfg['note'] ?? null,
            'storeRoute' => route('imports.store', $entity),
            'templateRoute' => route('imports.template', $entity),
            'backRoute' => route($cfg['index']),
        ]);
    }

    public function store(string $entity, Request $request)
    {
        abort_unless(Auth::user()?->isManager(), 403);
        $cfg = $this->config($entity);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $import = app($cfg['import']);
        Excel::import($import, $request->file('file'));

        $summary = __('portal.import.done', ['created' => $import->created, 'updated' => $import->updated, 'skipped' => count($import->failures)]);

        if (! empty($import->failures)) {
            return redirect()->route('imports.form', $entity)
                ->with('status', $summary)
                ->with('import_failures', $import->failures);
        }

        return redirect()->route($cfg['index'])->with('success', $summary)->with('status', $summary);
    }

    public function template(string $entity)
    {
        abort_unless(Auth::user()?->isManager(), 403);
        $cfg = $this->config($entity);

        return Excel::download(new GenericTemplateExport($cfg['columns'], $cfg['sample']), $entity.'-template.xlsx');
    }
}
