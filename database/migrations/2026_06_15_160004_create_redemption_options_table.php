<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Engagement Points — admin-configurable redemption catalog (spec §6, §9).
 * type: service_discount | ai_access | consultation | other
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redemption_options', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();        // discount_5, discount_10, discount_15, ai_run, ai_pass_30d, free_consultation
            $t->string('name');
            $t->string('name_ar')->nullable();
            $t->string('type', 32);
            $t->integer('cost_points')->default(0);
            $t->json('value_meta')->nullable();  // discount:{percent,cap_sar}; ai:{mode,days}; consultation:{minutes}
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redemption_options');
    }
};
