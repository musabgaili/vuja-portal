<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update idea_requests table
        Schema::table('idea_requests', function (Blueprint $table) {
            $table->string('quote_file_path')->nullable()->after('final_quote');
            $table->enum('quote_status', ['pending_approval', 'approved', 'rejected_by_client'])->nullable()->after('quote_file_path');
            $table->foreignId('quote_approved_by')->nullable()->constrained('users')->after('quote_status');
            $table->timestamp('quote_approved_at')->nullable()->after('quote_approved_by');
        });

        // Update consultation_requests table
        Schema::table('consultation_requests', function (Blueprint $table) {
            $table->decimal('quote_amount', 10, 2)->nullable()->after('meeting_link');
            $table->string('quote_file_path')->nullable()->after('quote_amount');
            $table->enum('quote_status', ['pending_approval', 'approved', 'rejected_by_client'])->nullable()->after('quote_file_path');
            $table->foreignId('quote_approved_by')->nullable()->constrained('users')->after('quote_status');
            $table->timestamp('quote_approved_at')->nullable()->after('quote_approved_by');
        });

        // Update research_requests table
        Schema::table('research_requests', function (Blueprint $table) {
            $table->decimal('quote_amount', 10, 2)->nullable()->after('meeting_link');
            $table->string('quote_file_path')->nullable()->after('quote_amount');
            $table->enum('quote_status', ['pending_approval', 'approved', 'rejected_by_client'])->nullable()->after('quote_file_path');
            $table->foreignId('quote_approved_by')->nullable()->constrained('users')->after('quote_status');
            $table->timestamp('quote_approved_at')->nullable()->after('quote_approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('idea_requests', function (Blueprint $table) {
            $table->dropColumn(['quote_file_path', 'quote_status', 'quote_approved_by', 'quote_approved_at']);
        });

        Schema::table('consultation_requests', function (Blueprint $table) {
            $table->dropColumn(['quote_amount', 'quote_file_path', 'quote_status', 'quote_approved_by', 'quote_approved_at']);
        });

        Schema::table('research_requests', function (Blueprint $table) {
            $table->dropColumn(['quote_amount', 'quote_file_path', 'quote_status', 'quote_approved_by', 'quote_approved_at']);
        });
    }
};
