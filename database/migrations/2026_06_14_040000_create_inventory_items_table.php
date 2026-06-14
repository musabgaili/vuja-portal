<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Intelligent Inventory for the AI Scope & Pricing Planner.
 * Internal cost + markup stay confidential; the client only ever sees a
 * category-grouped total.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');                         // Electronics, Structural, Labor, ...
            $table->decimal('internal_cost', 12, 2)->default(0); // what VujaDe pays (confidential)
            $table->decimal('markup_percentage', 5, 2)->default(30);
            $table->string('unit')->nullable();
            $table->string('sku')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
