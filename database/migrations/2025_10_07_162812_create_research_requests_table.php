<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('research_topic');
            $table->text('research_details')->nullable();
            $table->text('relevant_links')->nullable();
            $table->json('uploaded_files')->nullable();
            
            // Status: submitted -> nda_pending -> nda_signed -> details_provided -> meeting_scheduled -> in_progress -> completed
            $table->enum('status', [
                'submitted', 'nda_pending', 'nda_signed', 'details_provided',
                'meeting_scheduled', 'in_progress', 'completed', 'cancelled'
            ])->default('submitted');
            
            // NDA/SLA
            $table->timestamp('nda_signed_at')->nullable();
            $table->timestamp('sla_signed_at')->nullable();
            $table->string('nda_document')->nullable();
            $table->string('sla_document')->nullable();
            
            // Meeting
            $table->timestamp('meeting_scheduled_at')->nullable();
            $table->string('meeting_link')->nullable();
            $table->text('research_findings')->nullable();
            
            // Assignment
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_requests');
    }
};
