<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idea_request_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idea_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('comment');
            $table->decimal('suggested_price', 10, 2)->nullable();
            $table->boolean('is_internal')->default(false); // Internal notes vs client-visible
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idea_request_comments');
    }
};
