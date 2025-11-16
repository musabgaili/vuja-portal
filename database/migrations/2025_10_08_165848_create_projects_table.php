<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')
            ->constrained('users')
            ->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->text('scope')->nullable();
            
            // Link to original service request (polymorphic)
            $table->string('source_type')->nullable(); // IdeaRequest, ConsultationRequest, etc.
            $table->unsignedBigInteger('source_id')->nullable();
            
            $table->enum('status', [
                'planning', 'active', 'on_hold', 'completed', 'cancelled'
            ])->default('planning');
            
            $table->decimal('budget', 10, 2)->nullable();
            $table->decimal('spent', 10, 2)->default(0);
            
            $table->integer('completion_percentage')->default(0);
            
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            
            // Team
            $table->foreignId('project_manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->json('team_members')->nullable(); // Array of user IDs
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
