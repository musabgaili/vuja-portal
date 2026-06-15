<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Customer invoices. A manager/PM raises an invoice for a client (optionally
 * attaching the invoice document and linking it to a project / source quote).
 * The client uploads a payment receipt as proof → status moves from unpaid to
 * proof_submitted (the "pending" prompt clears); a manager then confirms paid.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();                                   // public route key
            $table->string('invoice_number')->unique();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('quote_id')->nullable()->constrained('quotes')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 8)->default('SAR');

            // unpaid -> proof_submitted (client uploaded receipt) -> paid ; or cancelled
            $table->string('status', 16)->default('unpaid');
            $table->date('due_date')->nullable();

            $table->string('invoice_file')->nullable();        // manager-attached document (private disk)
            $table->string('receipt_path')->nullable();        // client payment proof (private disk)
            $table->timestamp('receipt_uploaded_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();
            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
