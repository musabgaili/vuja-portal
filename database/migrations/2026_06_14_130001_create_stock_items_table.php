<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inventory products / SKUs. Separate from the existing InventoryItem (which is
 * a pricing/scope helper for the AI planner) — this is the physical-stock module
 * with a purchase price and three customer-tier selling prices.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();
            $table->string('product_id')->unique();        // user-facing SKU
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();       // stored on the 'public' disk

            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('price_student', 12, 2)->default(0);
            $table->decimal('price_entrepreneur', 12, 2)->default(0);
            $table->decimal('price_company', 12, 2)->default(0);

            $table->string('unit')->nullable();             // piece, meter, kg...
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_items');
    }
};
