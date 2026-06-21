<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Each @mention in a message → the mentioned user, with per-user read state. */
    public function up(): void
    {
        Schema::create('chat_message_mentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_message_id')->constrained('chat_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['chat_message_id', 'user_id']);
            $table->index(['user_id', 'read_at']);  // the "my unread mentions" lookup
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_message_mentions');
    }
};
