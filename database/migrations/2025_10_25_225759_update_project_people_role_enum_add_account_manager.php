<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For SQLite, we need to recreate the table to modify the enum constraint
        if (DB::getDriverName() === 'sqlite') {
            // Create a temporary table with the new enum values
            Schema::create('project_people_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->enum('role', ['project_manager', 'employee', 'client', 'account_manager']);
                $table->boolean('can_edit')->default(false);
                $table->timestamps();
                
                $table->unique(['project_id', 'user_id']);
            });

            // Copy data from old table to new table
            DB::statement('INSERT INTO project_people_temp SELECT * FROM project_people');

            // Drop old table and rename new table
            Schema::dropIfExists('project_people');
            Schema::rename('project_people_temp', 'project_people');
        } else {
            // For other databases, we can modify the enum directly
            DB::statement("ALTER TABLE project_people MODIFY COLUMN role ENUM('project_manager', 'employee', 'client', 'account_manager')");
        }
    }

    public function down(): void
    {
        // For SQLite, we need to recreate the table to modify the enum constraint
        if (DB::getDriverName() === 'sqlite') {
            // Create a temporary table with the old enum values
            Schema::create('project_people_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->enum('role', ['project_manager', 'employee', 'client']);
                $table->boolean('can_edit')->default(false);
                $table->timestamps();
                
                $table->unique(['project_id', 'user_id']);
            });

            // Copy data from old table to new table (excluding account_manager records)
            DB::statement("INSERT INTO project_people_temp SELECT * FROM project_people WHERE role != 'account_manager'");

            // Drop old table and rename new table
            Schema::dropIfExists('project_people');
            Schema::rename('project_people_temp', 'project_people');
        } else {
            // For other databases, we can modify the enum directly
            DB::statement("ALTER TABLE project_people MODIFY COLUMN role ENUM('project_manager', 'employee', 'client')");
        }
    }
};