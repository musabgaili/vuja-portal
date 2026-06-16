<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stamp when a quote is sent to the client, so "quotes produced" (a target
 * metric + a gated points action) can be measured by the send event rather than
 * by draft creation — a draft is not output.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $t) {
            $t->timestamp('sent_at')->nullable()->after('accepted_at');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $t) {
            $t->dropColumn('sent_at');
        });
    }
};
