<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('improvement_ideas', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();                                  // public route key
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();  // submitter (client, employee or PM)

            $table->string('title');
            $table->string('category')->nullable();          // area of the portal the idea touches
            $table->text('description');                      // the idea itself
            $table->text('technology_used')->nullable();      // tools/tech the idea would use
            $table->text('benefit')->nullable();              // why it helps / expected impact

            // submitted -> approved | rejected ; an approved idea may later be marked implemented.
            // Engagement points are only awarded on manager approval.
            $table->enum('status', ['submitted', 'approved', 'rejected', 'implemented'])->default('submitted');

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('improvement_ideas');
    }
};
