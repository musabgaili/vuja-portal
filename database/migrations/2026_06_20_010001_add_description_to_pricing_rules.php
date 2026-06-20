<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A client-facing service description for the Pricing Tool. Distinct from the
     * existing required 'note' (a short internal detail): this optional, longer
     * text is what the Scope Planner recalls when a pre-defined service is chosen.
     */
    public function up(): void
    {
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->text('description')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
