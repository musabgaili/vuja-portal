<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Project proposals: any internal staff member may PROPOSE a project; a
     * manager or global project manager reviews it and either approves
     * (the project starts) or sends it back with a comment. These columns
     * carry the proposal lifecycle alongside the existing project `status`
     * (a new "proposed" status value gates the project until approval).
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('proposed_by')->nullable()->after('quoted_at')
                ->constrained('users')->nullOnDelete();
            $table->text('proposal_notes')->nullable()->after('proposed_by');
            $table->foreignId('proposal_reviewed_by')->nullable()->after('proposal_notes')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('proposal_reviewed_at')->nullable()->after('proposal_reviewed_by');
            $table->text('proposal_review_notes')->nullable()->after('proposal_reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('proposed_by');
            $table->dropConstrainedForeignId('proposal_reviewed_by');
            $table->dropColumn(['proposal_notes', 'proposal_reviewed_at', 'proposal_review_notes']);
        });
    }
};
