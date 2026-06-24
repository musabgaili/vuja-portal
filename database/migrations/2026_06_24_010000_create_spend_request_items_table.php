<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Line items for a spend/purchase request — one request can now list several
 * items, each with its own quantity, unit cost and (for purchases) its own link.
 * The request's `amount` becomes the sum of the line totals.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spend_request_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('spend_request_id')->constrained()->cascadeOnDelete();
            $t->string('description');
            $t->unsignedInteger('quantity')->default(1);
            $t->decimal('unit_amount', 12, 2)->default(0);
            $t->string('product_url', 1000)->nullable(); // where to buy this line
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();

            $t->index('spend_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spend_request_items');
    }
};
