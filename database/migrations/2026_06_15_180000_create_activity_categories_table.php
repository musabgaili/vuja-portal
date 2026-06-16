<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Capacity layer (spec §6) — the time/target buckets (configurable). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_categories', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();        // printing_3d, electronic_design, software_dev, prototyping, pre_sales, internal, sales
            $t->string('name');
            $t->string('name_ar')->nullable();
            $t->string('kind', 16)->default('delivery'); // delivery | business_dev | internal
            $t->boolean('is_billable')->default(true);
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_categories');
    }
};
