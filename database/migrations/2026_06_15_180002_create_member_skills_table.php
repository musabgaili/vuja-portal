<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Capacity layer (spec §6) — which delivery categories an engineer covers. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_skills', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_member_id')->constrained()->cascadeOnDelete();
            $t->foreignId('activity_category_id')->constrained()->cascadeOnDelete();
            $t->unique(['team_member_id', 'activity_category_id']);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_skills');
    }
};
