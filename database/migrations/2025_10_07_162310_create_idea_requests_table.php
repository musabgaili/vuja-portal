<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('idea_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('target_market')->nullable();
            $table->text('problem_solving')->nullable();
            $table->text('unique_value')->nullable();
            
            // Status workflow: draft -> submitted -> ai_assessment -> negotiation -> quoted -> accepted/rejected -> payment -> approved
            $table->enum('status', [
                'draft', 'submitted', 'ai_assessment', 'negotiation', 
                'quoted', 'accepted', 'rejected', 'payment_pending', 'approved', 'in_progress', 'completed'
            ])->default('draft');
            
            // AI Assessment
            $table->json('ai_assessment_data')->nullable();
            $table->integer('tokens_used')->default(0);
            
            // Negotiation
            $table->text('negotiation_notes')->nullable();
            $table->decimal('initial_quote', 10, 2)->nullable();
            $table->decimal('final_quote', 10, 2)->nullable();
            
            // Agreement
            $table->text('agreement_terms')->nullable();
            $table->timestamp('agreement_accepted_at')->nullable();
            $table->string('payment_file')->nullable();
            $table->timestamp('payment_verified_at')->nullable();
            
            // Assignment
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idea_requests');
    }
};
