<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Manager-editable AI prompt templates for the Scope Planner. Each row holds
     * one template keyed by name (generate_system / generate_user / suggest_system);
     * when no row exists (or it is blank), the code falls back to the hardcoded
     * default in config('scope.prompts.*'). The response schema + JSON validation
     * stay in code, so an edited prompt can never break structured generation.
     */
    public function up(): void
    {
        Schema::create('scope_prompt_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('content')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scope_prompt_settings');
    }
};
