<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Capacity layer (spec §6) — leave/holidays that reduce available hours. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_entries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_member_id')->constrained()->cascadeOnDelete();
            $t->date('date');
            $t->decimal('hours', 6, 2)->default(8);
            $t->string('type', 16)->default('leave');       // leave | holiday
            $t->timestamps();
            $t->index(['team_member_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_entries');
    }
};
