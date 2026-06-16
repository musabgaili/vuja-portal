<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Back the app-level dedupe with a DB uniqueness guard so a race / import can't
 * insert duplicate assignments that inflate recognized revenue. (Rows with a NULL
 * project_line_id are still deduped at the application layer, since SQL treats
 * NULLs as distinct in a unique index.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_assignments', function (Blueprint $t) {
            $t->unique(
                ['project_id', 'project_line_id', 'team_member_id', 'activity_category_id'],
                'project_assignments_unique_tuple'
            );
        });
    }

    public function down(): void
    {
        Schema::table('project_assignments', function (Blueprint $t) {
            $t->dropUnique('project_assignments_unique_tuple');
        });
    }
};
