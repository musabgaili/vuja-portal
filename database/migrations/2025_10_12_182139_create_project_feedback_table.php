<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            
            $table->integer('rating')->unsigned(); // 1-5 stars
            $table->text('feedback')->nullable();
            
            $table->integer('communication_rating')->unsigned()->nullable();
            $table->integer('quality_rating')->unsigned()->nullable();
            $table->integer('timeline_rating')->unsigned()->nullable();
            
            $table->boolean('would_recommend')->default(false);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_feedback');
    }
};
