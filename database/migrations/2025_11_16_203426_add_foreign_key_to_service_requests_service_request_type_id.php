<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            // Add foreign key for service_request_type_id
            $table->foreign('service_request_type_id')
                  ->references('id')
                  ->on('service_request_types')
                  ->onDelete('set null');
            
            // Add foreign key for current_step_id
            $table->foreign('current_step_id')
                  ->references('id')
                  ->on('service_request_steps')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign(['service_request_type_id']);
            $table->dropForeign(['current_step_id']);
        });
    }
};
