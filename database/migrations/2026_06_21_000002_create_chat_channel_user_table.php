<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Channel membership + per-user read state. */
    public function up(): void
    {
        Schema::create('chat_channel_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_channel_id')->constrained('chat_channels')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 12)->default('member');     // member | admin (channel admin)
            $table->unsignedBigInteger('last_read_message_id')->nullable(); // unread tracking (no FK: messages may be pruned)
            $table->boolean('muted')->default(false);
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['chat_channel_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_channel_user');
    }
};
