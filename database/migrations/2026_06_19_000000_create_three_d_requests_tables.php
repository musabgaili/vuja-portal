<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('three_d_requests', function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid')->unique();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('type')->default('printing'); // printing | design

            $t->string('title');
            $t->text('title_en')->nullable();
            $t->text('title_ar')->nullable();
            $t->text('description');
            $t->text('description_en')->nullable();
            $t->text('description_ar')->nullable();

            // Printing
            $t->string('material')->nullable();
            $t->string('color')->nullable();
            $t->unsignedInteger('quantity')->nullable();
            $t->string('dimensions')->nullable();
            $t->string('finish')->nullable();

            // Design
            $t->string('output_format')->nullable();
            $t->string('complexity')->nullable();
            $t->text('reference_links')->nullable();

            // Shared
            $t->string('budget_range')->nullable();
            $t->string('timeline')->nullable();

            $t->enum('status', ['submitted', 'assigned', 'in_progress', 'completed', 'rejected', 'cancelled'])->default('submitted');
            $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $t->text('manager_notes')->nullable();
            $t->timestamps();
            $t->index('status');
            $t->index(['user_id', 'type']);
        });

        Schema::create('three_d_request_files', function (Blueprint $t) {
            $t->id();
            $t->foreignId('three_d_request_id')->constrained('three_d_requests')->cascadeOnDelete();
            $t->string('path');
            $t->string('original_name');
            $t->string('mime')->nullable();
            $t->unsignedBigInteger('size')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('three_d_request_files');
        Schema::dropIfExists('three_d_requests');
    }
};
