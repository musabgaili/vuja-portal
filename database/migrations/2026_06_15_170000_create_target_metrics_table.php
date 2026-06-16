<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance Targets — the configurable catalog of metrics a manager can set a
 * target for. Auto metrics are counted live from existing activity; manual
 * metrics hold a value the manager enters. `engagement_action` links a metric to
 * the impact-points action it gates (points flow only once the target is hit).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('target_metrics', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();            // quotes_produced, projects_won, meetings_attended, projects_closed, ...
            $t->string('name');
            $t->string('name_ar')->nullable();
            $t->string('source', 16)->default('auto');   // auto | manual
            $t->string('unit', 16)->default('count');    // count | sar
            $t->string('engagement_action')->nullable(); // impact-points action this metric gates (nullable)
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_metrics');
    }
};
