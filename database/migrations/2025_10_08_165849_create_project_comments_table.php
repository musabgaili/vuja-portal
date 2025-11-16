<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_comments', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relation - can comment on projects, milestones, or tasks
            $table->string('commentable_type');
            $table->unsignedBigInteger('commentable_id');
            
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('comment');
            
            $table->boolean('is_internal')->default(false); // Internal notes vs client-visible
            
            $table->timestamps();
            
            $table->index(['commentable_type', 'commentable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_comments');
    }
};
