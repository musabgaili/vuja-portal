<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Inventory lab locations. Seeded with Malas, Noura and Monshaat but stored in a
 * table (not hard-coded) so more labs can be added later — every inventory form,
 * filter and Excel column iterates over this table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();   // malas | noura | monshaat
            $table->timestamps();
        });

        foreach (['Malas' => 'malas', 'Noura' => 'noura', 'Monshaat' => 'monshaat'] as $name => $slug) {
            DB::table('labs')->updateOrInsert(
                ['slug' => $slug],
                ['name' => $name, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('labs');
    }
};
