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
        Schema::create('step_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_step_id')->constrained()->onDelete('cascade');
            $table->string('field_name'); // e.g., "idea_description", "budget_range"
            $table->string('field_label'); // e.g., "Describe your idea", "Budget Range"
            $table->string('field_type'); // text, textarea, select, file, number, email, etc.
            $table->text('field_description')->nullable();
            $table->json('field_options')->nullable(); // For select, radio, checkbox options
            $table->json('validation_rules')->nullable(); // Laravel validation rules
            $table->boolean('is_required')->default(false);
            $table->integer('field_order')->default(0);
            $table->json('field_config')->nullable(); // Additional field configuration
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('step_form_fields');
    }
};
