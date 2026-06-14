<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets quotes draw line items from the StockItem inventory (labs + 3-tier
 * pricing) in addition to the legacy InventoryItem. customer_category on the
 * quote selects which StockItem tier price applies.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('customer_category')->nullable()->after('client_id'); // student|entrepreneur|company
        });

        Schema::table('quote_items', function (Blueprint $table) {
            $table->foreignId('stock_item_id')->nullable()->after('inventory_item_id')
                ->constrained('stock_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropConstrainedForeignKey('stock_item_id');
        });
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('customer_category');
        });
    }
};
