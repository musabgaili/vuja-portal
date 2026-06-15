<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extend `quote_items` so a line can be a tier-priced inventory COMPONENT
 * (rolled up for the client), a fixed-rate SERVICE from the Pricing Tool, or a
 * manual OTHER line — and so it can belong to a specific Company scope.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->string('type', 12)->default('component')->after('category');   // component|service|other
            $table->string('source', 12)->default('inventory')->after('type');     // inventory|pricing_tool|manual
            $table->foreignId('pricing_rule_id')->nullable()->after('stock_item_id')->constrained('pricing_rules')->nullOnDelete();
            $table->foreignId('quote_scope_id')->nullable()->after('pricing_rule_id')->constrained('quote_scopes')->nullOnDelete();
            $table->string('unit')->nullable()->after('qty');
            $table->decimal('unit_price', 12, 2)->default(0)->after('unit');
            $table->boolean('is_client_visible')->default(true)->after('line_client');
            $table->unsignedInteger('sort_order')->default(0)->after('is_client_visible');
        });
    }

    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pricing_rule_id');
            $table->dropConstrainedForeignId('quote_scope_id');
            $table->dropColumn(['type', 'source', 'unit', 'unit_price', 'is_client_visible', 'sort_order']);
        });
    }
};
