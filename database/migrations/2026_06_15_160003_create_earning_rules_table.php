<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Engagement Points — admin-configurable earning catalog (spec §6, §7).
 * Auto rules fire from hooks; claim rules require a submitted claim (§11).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('earning_rules', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();        // idea_accepted, social_post, referral_signup, ...
            $t->string('name');
            $t->string('name_ar')->nullable();
            $t->integer('points')->default(0);
            $t->boolean('is_active')->default(true);
            $t->boolean('auto_award')->default(true);        // false => requires a claim (§11)
            $t->boolean('requires_review')->default(false);  // manual approval before crediting
            $t->integer('cap_count')->nullable();            // e.g. social: 2 per period; idea: 5 before review
            $t->string('cap_period', 16)->nullable();        // month | year | lifetime
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('earning_rules');
    }
};
