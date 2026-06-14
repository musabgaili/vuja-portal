<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the internal approval layer to quotes: an approver audit trail and a
 * comment thread (manager feedback / request-changes notes / discussion).
 * The status column is a string, so the new values (pending_approval, approved,
 * changes_requested) need no schema change.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('accepted_at');
        });

        Schema::create('quote_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type')->default('note');   // note | approval | rejection | changes | submitted
            $table->text('comment');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_comments');
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropConstrainedForeignKey('approved_by');
            $table->dropColumn('approved_at');
        });
    }
};
