<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite: Drop and recreate column as nullable boolean
        DB::statement('ALTER TABLE project_milestones DROP COLUMN client_approved');
        
        Schema::table('project_milestones', function (Blueprint $table) {
            $table->boolean('client_approved')->nullable()->after('completion_percentage');
        });
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE project_milestones DROP COLUMN client_approved');
        
        Schema::table('project_milestones', function (Blueprint $table) {
            $table->boolean('client_approved')->default(false);
        });
    }
};
