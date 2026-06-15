<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Companion tables for the upgraded AI Scope Planner. These hang off the
 * existing `quotes` table (we extend, not replace it): per-scope sections for
 * multi-scope Company documents, the milestone payment schedule, and editable
 * bilingual boilerplate text.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('title');
            $table->enum('type', ['commercial', 'sponsored'])->default('commercial');
            $table->json('objective')->nullable();
            $table->json('inputs_required')->nullable();
            $table->json('deliverables')->nullable();
            $table->json('acceptance_criteria')->nullable();
            $table->json('exclusions')->nullable();
            $table->decimal('price', 12, 2)->default(0);   // = sum of its line items
            $table->timestamps();
        });

        Schema::create('quote_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->string('code');                 // M1, M2, M3
            $table->string('trigger')->nullable();  // editable text
            $table->decimal('percentage', 5, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('scope_boilerplate', function (Blueprint $table) {
            $table->id();
            $table->enum('tier', ['student', 'entrepreneur', 'company', 'all'])->default('all');
            $table->enum('language', ['ar', 'en'])->default('en');
            $table->string('section_key');   // pricing_intro, notes, terms, confidentiality, validity...
            $table->longText('content')->nullable();
            $table->timestamps();
            $table->unique(['tier', 'language', 'section_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scope_boilerplate');
        Schema::dropIfExists('quote_milestones');
        Schema::dropIfExists('quote_scopes');
    }
};
