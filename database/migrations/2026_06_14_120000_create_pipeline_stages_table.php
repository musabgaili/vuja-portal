<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Manager-editable CRM pipeline stages. Replaces the hard-coded
 * config('crm.stages') list — stages become DB rows a manager can add, rename,
 * recolour, reorder and (de)activate. The terminal 'won'/'lost' outcomes stay
 * as constants and are intentionally NOT stored here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipeline_stages', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();        // stable slug stored on opportunities.stage
            $table->string('label');
            $table->string('color')->default('secondary'); // Bootstrap colour name
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'position']);
        });

        // Seed from the previous config defaults so existing pipelines keep working.
        $seed = config('crm.stages', ['new' => 'New', 'qualified' => 'Qualified', 'proposition' => 'Proposition']);
        $colors = ['new' => 'secondary', 'qualified' => 'primary', 'proposition' => 'info'];
        $position = 1;
        foreach ($seed as $key => $label) {
            DB::table('pipeline_stages')->updateOrInsert(
                ['key' => $key],
                [
                    'label' => $label,
                    'color' => $colors[$key] ?? 'secondary',
                    'position' => $position++,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pipeline_stages');
    }
};
