<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bilingual service names for the Pricing Tool so the BoQ can render the service
 * line in the document language. Existing `item` seeds the English name.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('item');
            $table->string('name_ar')->nullable()->after('name_en');
        });

        // Backfill the English name from the existing free-text `item`.
        DB::table('pricing_rules')->whereNull('name_en')->update([
            'name_en' => DB::raw('item'),
        ]);
    }

    public function down(): void
    {
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'name_ar']);
        });
    }
};
