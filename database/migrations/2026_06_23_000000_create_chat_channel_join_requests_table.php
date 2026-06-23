<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Requests by internal staff to join a PUBLIC team channel they're not in.
 * A channel admin / creator / manager approves or declines them.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('chat_channel_join_requests')) {
            return;
        }

        Schema::create('chat_channel_join_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_channel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending | approved | declined
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->unique(['chat_channel_id', 'user_id']);
            $table->index(['chat_channel_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_channel_join_requests');
    }
};
