<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Capacity layer (spec §6) — a week's split across categories (lines sum to 100%). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_allocation_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('weekly_allocation_id')->constrained()->cascadeOnDelete();
            $t->foreignId('activity_category_id')->constrained()->cascadeOnDelete();
            $t->decimal('percent', 5, 2)->default(0);       // share of the week
            $t->decimal('hours', 6, 2)->default(0);         // cached = percent/100 * available_hours
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_allocation_lines');
    }
};
