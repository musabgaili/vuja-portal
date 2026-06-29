<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Scope-document customization layer.
 *
 * doc_labels   — targeted editor (Phase 2A): per-quote overrides for section
 *                headings and table column headers, e.g.
 *                {"col.value":"Price","heading.pricing_structure":"Investment"}.
 *                The document Blades read these with a fallback to the default
 *                scope.* translation, so an empty value keeps the standard label.
 *
 * custom_tables — advanced grid editor (Phase 2B): per-quote free-form table
 *                overrides keyed by a table slug, each a generic grid
 *                {columns:[{label,align}], rows:[[cell,...]], merges:[...]}.
 *                When a slug is present the renderer draws the custom grid instead
 *                of the structured one. Pricing money cells stay system-computed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            if (! Schema::hasColumn('quotes', 'doc_labels')) {
                $table->json('doc_labels')->nullable()->after('ai_content');
            }
            if (! Schema::hasColumn('quotes', 'custom_tables')) {
                $table->json('custom_tables')->nullable()->after('doc_labels');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            foreach (['doc_labels', 'custom_tables'] as $col) {
                if (Schema::hasColumn('quotes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
