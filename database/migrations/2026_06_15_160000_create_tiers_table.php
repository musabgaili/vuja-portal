<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Engagement Points — status tiers (admin-configurable). A client's tier is
 * driven by lifetime points (never reduced by spending). See spec §12.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiers', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();        // explorer, innovator, pioneer, partner
            $t->string('name');
            $t->string('name_ar')->nullable();
            $t->integer('min_lifetime_points')->default(0);
            $t->json('perks')->nullable();
            $t->string('badge')->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiers');
    }
};
