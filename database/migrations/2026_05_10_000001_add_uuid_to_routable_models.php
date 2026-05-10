<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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
        foreach ($this->tables as $table) {
            if (! Schema::hasColumn($table, 'uuid')) {
                Schema::table($table, function (Blueprint $table): void {
                    $table->char('uuid', 36)->nullable()->unique()->after('id');
                });
            }

            DB::table($table)
                ->whereNull('uuid')
                ->orderBy('id')
                ->select('id')
                ->chunkById(100, function ($rows) use ($table): void {
                    foreach ($rows as $row) {
                        DB::table($table)
                            ->where('id', $row->id)
                            ->update(['uuid' => (string) Str::uuid()]);
                    }
                });
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $table) {
            if (Schema::hasColumn($table, 'uuid')) {
                Schema::table($table, function (Blueprint $table): void {
                    $table->dropUnique(['uuid']);
                    $table->dropColumn('uuid');
                });
            }
        }
    }
};
