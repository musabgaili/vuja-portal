<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Engagement Points — discretionary grants (spec §6, §13).
 * A Project Manager suggests; only a Manager may approve. PM alone grants nothing.
 * status: suggested | approved | rejected
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_grant_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $t->integer('points');
            $t->text('reason');
            $t->string('status', 16)->default('suggested');
            $t->foreignId('suggested_by')->constrained('users')->cascadeOnDelete();      // Project Manager
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete(); // Manager
            $t->foreignId('points_transaction_id')->nullable()->constrained('points_transactions')->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_grant_requests');
    }
};
