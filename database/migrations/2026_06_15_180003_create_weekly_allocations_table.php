<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Capacity layer (spec §6, §9) — one allocation per engineer per ISO week. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_allocations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_member_id')->constrained()->cascadeOnDelete();
            $t->date('week_start');                         // Monday
            $t->decimal('available_hours', 6, 2)->default(0); // capacity − leave that week (cached)
            $t->string('status', 16)->default('draft');     // draft | submitted
            $t->timestamps();
            $t->unique(['team_member_id', 'week_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_allocations');
    }
};
