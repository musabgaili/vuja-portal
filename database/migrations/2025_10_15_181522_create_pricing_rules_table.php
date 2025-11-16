<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('item'); // e.g., "3D Design", "Development"
            $table->decimal('rate', 10, 2); // Price per unit
            $table->string('unit'); // e.g., "hour", "page", "model"
            $table->string('level'); // e.g., "beginner", "expert"
            $table->text('note'); // Description/details
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
