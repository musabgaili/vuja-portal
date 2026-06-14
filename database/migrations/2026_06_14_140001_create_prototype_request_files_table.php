<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Supporting documents uploaded with a prototype request. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prototype_request_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prototype_request_id')->constrained()->cascadeOnDelete();
            $table->string('path');             // stored on the 'public' disk
            $table->string('original_name');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prototype_request_files');
    }
};
