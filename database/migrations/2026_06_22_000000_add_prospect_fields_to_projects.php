<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Free-text "prospect" fields for a proposed project whose client is NOT yet a
 * registered account (recorded without creating/inviting an account).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'prospect_name')) {
                $table->string('prospect_name')->nullable()->after('client_id');
            }
            if (! Schema::hasColumn('projects', 'prospect_email')) {
                $table->string('prospect_email')->nullable()->after('prospect_name');
            }
            if (! Schema::hasColumn('projects', 'prospect_phone')) {
                $table->string('prospect_phone')->nullable()->after('prospect_email');
            }
            if (! Schema::hasColumn('projects', 'prospect_company')) {
                $table->string('prospect_company')->nullable()->after('prospect_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            foreach (['prospect_name', 'prospect_email', 'prospect_phone', 'prospect_company'] as $col) {
                if (Schema::hasColumn('projects', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
