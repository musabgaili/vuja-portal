<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A per-line free-text description for quote items. Backs both a MANUAL
     * service's typed description and a PRE-DEFINED service's description that the
     * planner auto-recalls from its Pricing Tool rule.
     */
    public function up(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
