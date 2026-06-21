<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A conversation: a team channel (type=channel) or a direct/group message
     * (type=dm). DMs are auto-named from their participants and are always private.
     */
    public function up(): void
    {
        Schema::create('chat_channels', function (Blueprint $table) {
            $table->id();
            $table->string('type', 12)->default('channel');   // channel | dm
            $table->string('name')->nullable();               // null for DMs (derived from members)
            $table->text('description')->nullable();
            $table->boolean('is_private')->default(false);     // channels: invite-only; DMs always private
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_message_at')->nullable();  // for ordering the conversation list
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'last_message_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_channels');
    }
};
