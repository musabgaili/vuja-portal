<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stock per lab — the join between stock_items and labs, with the quantity on
 * the pivot. Total stock for an item = sum of its rows here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_stock_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lab_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->timestamps();

            $table->unique(['stock_item_id', 'lab_id']);   // one stock row per item per lab
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_stock_item');
    }
};
