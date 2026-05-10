<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables = [
        'projects',
        'idea_requests',
        'consultation_requests',
        'research_requests',
        'ip_registrations',
        'copyright_registrations',
        'project_documents',
        'project_deliverables',
        'project_milestones',
        'meetings',
    ];

    public function up(): void
    {
        $driver = DB::getDriverName();

        foreach ($this->tables as $table) {
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE `{$table}` MODIFY `uuid` CHAR(36) NOT NULL");
            } elseif ($driver === 'pgsql') {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN uuid SET NOT NULL");
            }
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        foreach ($this->tables as $table) {
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE `{$table}` MODIFY `uuid` CHAR(36) NULL");
            } elseif ($driver === 'pgsql') {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN uuid DROP NOT NULL");
            }
        }
    }
};
