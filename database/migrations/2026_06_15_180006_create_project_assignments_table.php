<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Capacity layer (spec §6, §11) — an engineer ↔ project service line, for revenue
 * attribution. Recognized in the month the project is delivered/completed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_assignments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_member_id')->constrained()->cascadeOnDelete();
            $t->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $t->unsignedBigInteger('project_line_id')->nullable(); // QuoteItem id (service line), if line-level
            $t->foreignId('activity_category_id')->constrained()->cascadeOnDelete();
            $t->decimal('value', 12, 2)->default(0);               // line value (or member's share)
            $t->timestamps();
            $t->index(['project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_assignments');
    }
};
