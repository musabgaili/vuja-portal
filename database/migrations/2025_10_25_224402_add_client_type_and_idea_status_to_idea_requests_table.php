<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('idea_requests', function (Blueprint $table) {
            $table->enum('client_type', ['individual', 'company'])->after('user_id');
            $table->enum('idea_status', ['seeking_around', 'ready', 'running_project', 'concept_only'])->after('client_type');
        });
    }

    public function down(): void
    {
        Schema::table('idea_requests', function (Blueprint $table) {
            $table->dropColumn(['client_type', 'idea_status']);
        });
    }
};