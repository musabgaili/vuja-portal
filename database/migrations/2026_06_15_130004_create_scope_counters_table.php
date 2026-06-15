<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Atomic sequence counter for Q-series quote numbers. A single locked row per
 * key avoids collisions under concurrent quote creation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scope_counters', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->unsignedBigInteger('value')->default(0);
            $table->timestamps();
        });

        DB::table('scope_counters')->insert([
            'key' => 'quote',
            'value' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('scope_counters');
    }
};
