<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lightweight payment tracking so an accepted quote can act as the client's
 * invoice: unpaid -> paid, with the date it was marked paid. The client sees
 * accepted quotes as invoices (linked to their project) and the pending total;
 * a manager marks them paid.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('payment_status', 12)->default('unpaid')->after('status'); // unpaid|paid
            $table->timestamp('paid_at')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'paid_at']);
        });
    }
};
