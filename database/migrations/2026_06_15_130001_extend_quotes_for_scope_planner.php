<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extend the existing `quotes` table for the upgraded Scope Planner: a Q-series
 * number, language/length/structure, document meta, the AI section prose, and
 * the VAT-bearing money breakdown. Strings (not enums) for the small choice
 * columns to match the existing `status`/`customer_category` convention and to
 * keep SQLite ALTERs painless.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('quote_number')->nullable()->unique()->after('id');
            $table->string('language', 2)->default('en')->after('customer_category');     // ar|en
            $table->string('length', 10)->default('medium')->after('language');           // short|medium|long
            $table->string('structure', 12)->default('single')->after('length');          // single|multi_scope
            $table->string('subject')->nullable()->after('title');
            $table->string('beneficiary')->nullable()->after('subject');
            $table->string('client_ref')->nullable()->after('beneficiary');
            $table->text('brief')->nullable()->after('scope');
            $table->json('ai_content')->nullable()->after('brief');

            $table->decimal('vat_rate', 5, 2)->default(15.00)->after('total_client');
            $table->decimal('components_internal_total', 12, 2)->default(0)->after('vat_rate');
            $table->decimal('components_client_total', 12, 2)->nullable()->after('components_internal_total');
            $table->decimal('subtotal', 12, 2)->default(0)->after('components_client_total');
            $table->decimal('vat_amount', 12, 2)->default(0)->after('subtotal');
            $table->decimal('grand_total', 12, 2)->default(0)->after('vat_amount');
            $table->unsignedInteger('validity_days')->default(30)->after('valid_until');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropUnique(['quote_number']);
            $table->dropColumn([
                'quote_number', 'language', 'length', 'structure', 'subject', 'beneficiary',
                'client_ref', 'brief', 'ai_content', 'vat_rate', 'components_internal_total',
                'components_client_total', 'subtotal', 'vat_amount', 'grand_total', 'validity_days',
            ]);
        });
    }
};
