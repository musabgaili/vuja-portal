<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance indexes for the hot dashboard/heatmap/notification queries flagged
 * by the performance audit: status filters and FK+status composites that were
 * doing full scans on MySQL prod. Each add is guarded (table/column existence +
 * duplicate-safe) so the migration is idempotent across MySQL and sqlite.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->add('projects', ['status'], 'projects_status_index');

        foreach (['idea_requests', 'consultation_requests', 'research_requests', 'ip_registrations', 'copyright_registrations'] as $t) {
            $this->add($t, ['user_id', 'status'], $t.'_user_status_index');
            $this->add($t, ['assigned_to', 'status'], $t.'_assigned_status_index');
        }

        $this->add('users', ['type'], 'users_type_index');

        $this->add('project_tasks', ['assigned_to', 'status'], 'project_tasks_assigned_status_index');
        $this->add('project_tasks', ['assigned_to', 'updated_at'], 'project_tasks_assigned_updated_index');
        $this->add('project_tasks', ['project_id', 'status'], 'project_tasks_project_status_index');

        $this->add('engagement_logs', ['user_id', 'created_at'], 'engagement_logs_user_created_index');
    }

    public function down(): void
    {
        $drop = [
            'projects' => ['projects_status_index'],
            'users' => ['users_type_index'],
            'project_tasks' => ['project_tasks_assigned_status_index', 'project_tasks_assigned_updated_index', 'project_tasks_project_status_index'],
            'engagement_logs' => ['engagement_logs_user_created_index'],
        ];
        foreach (['idea_requests', 'consultation_requests', 'research_requests', 'ip_registrations', 'copyright_registrations'] as $t) {
            $drop[$t] = [$t.'_user_status_index', $t.'_assigned_status_index'];
        }
        foreach ($drop as $table => $names) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach ($names as $name) {
                try {
                    Schema::table($table, fn (Blueprint $t) => $t->dropIndex($name));
                } catch (\Throwable $e) {
                }
            }
        }
    }

    private function add(string $table, array $columns, string $name): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }
        foreach ($columns as $c) {
            if (! Schema::hasColumn($table, $c)) {
                return;
            }
        }
        try {
            Schema::table($table, fn (Blueprint $t) => $t->index($columns, $name));
        } catch (\Throwable $e) {
            // Index already exists — safe to ignore (idempotent).
        }
    }
};
