<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Capacity layer (spec §6) — engineers tracked for allocation/utilization. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $t->string('display_name');
            $t->string('specialization')->nullable();   // 'design' | 'electronics' (label only)
            $t->decimal('weekly_capacity_hours', 6, 2)->default(40);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
