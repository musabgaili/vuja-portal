<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_work_items', function (Blueprint $table) {
            $table->id();
            $table->morphs('noteable');               // noteable_type + noteable_id
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');                   // note | deliverable
            $table->text('content')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->boolean('is_client_visible')->default(false);  // deliverables shared back to the client
            $table->timestamps();

            $table->index(['noteable_type', 'noteable_id', 'type'], 'swi_noteable_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_work_items');
    }
};
