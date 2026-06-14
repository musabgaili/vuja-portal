<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Turns scope changes into a formal change-request workflow: a budget delta the
 * change carries, plus a digital client signature that applies it (the only
 * sanctioned way to move a locked budget — see Project budget lock).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_scope_changes', function (Blueprint $table) {
            $table->decimal('budget_delta', 12, 2)->nullable()->after('justification');
            $table->string('client_signature')->nullable()->after('review_notes');
            $table->timestamp('client_signed_at')->nullable()->after('client_signature');
            $table->string('client_ip', 45)->nullable()->after('client_signed_at');
            $table->timestamp('applied_at')->nullable()->after('client_ip');
        });
    }

    public function down(): void
    {
        Schema::table('project_scope_changes', function (Blueprint $table) {
            $table->dropColumn(['budget_delta', 'client_signature', 'client_signed_at', 'client_ip', 'applied_at']);
        });
    }
};
