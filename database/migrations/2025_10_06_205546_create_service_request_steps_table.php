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
        Schema::create('service_request_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_type_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Initial Idea", "AI Assessment"
            $table->text('description')->nullable();
            $table->integer('step_order'); // Order of steps
            $table->string('step_type'); // form, approval, assignment, external_api
            $table->json('step_config')->nullable(); // Configuration for the step
            $table->json('conditions')->nullable(); // Conditions to show this step
            $table->json('actions')->nullable(); // Actions after step completion
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_request_steps');
    }
};
