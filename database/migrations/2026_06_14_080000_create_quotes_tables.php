<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quotes — the bridge between the AI Scope/Pricing Planner and the CRM.
 * A quote snapshots the scope + priced line items; client acceptance (signature)
 * turns it into an order (a Project), closing the sales loop.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->nullable()->constrained('opportunities')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('title');
            $table->longText('scope')->nullable();
            $table->string('status')->default('draft'); // draft|sent|accepted|rejected
            $table->decimal('total_internal', 12, 2)->default(0);
            $table->decimal('total_client', 12, 2)->default(0);
            $table->date('valid_until')->nullable();
            $table->string('accepted_signature')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->string('accepted_ip', 45)->nullable();
            $table->text('reject_reason')->nullable();
            $table->timestamps();
            $table->index('status');
        });

        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->nullable()->constrained('inventory_items')->nullOnDelete();
            $table->string('name');
            $table->string('category');
            $table->decimal('internal_cost', 12, 2)->default(0);
            $table->decimal('markup_percentage', 5, 2)->default(0);
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('line_internal', 12, 2)->default(0);
            $table->decimal('line_client', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
        Schema::dropIfExists('quotes');
    }
};
