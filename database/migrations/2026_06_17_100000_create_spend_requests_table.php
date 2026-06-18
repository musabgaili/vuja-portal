<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spend_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('requester_id')->constrained('users')->cascadeOnDelete();

            // project = tied to a project budget; general = company/non-project spend.
            $t->string('scope')->default('project');
            $t->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();

            // reimbursement = "I paid, pay me back"; purchase = "buy this for me".
            $t->string('type')->default('reimbursement');

            $t->string('title');
            $t->text('description')->nullable();
            $t->string('category')->nullable();
            $t->decimal('amount', 12, 2)->default(0);   // paid (reimbursement) / estimate (purchase)
            $t->string('currency', 8)->default('SAR');
            $t->string('product_url')->nullable();       // purchase: where to buy
            $t->unsignedInteger('quantity')->default(1);
            $t->string('receipt_file')->nullable();      // reimbursement proof (private disk)

            // pending -> approved -> completed  (or rejected). "approved" purchases are
            // "committed" against the budget until marked purchased.
            $t->string('status')->default('pending');
            $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('reviewed_at')->nullable();
            $t->text('review_notes')->nullable();

            // Purchase fulfilment (the real cost once bought).
            $t->decimal('actual_amount', 12, 2)->nullable();
            $t->string('actual_receipt_file')->nullable();
            $t->timestamp('completed_at')->nullable();

            // Option A: a lone manager self-records their own spend (flagged for audit).
            $t->boolean('self_recorded')->default(false);

            // Link to the posted ledger row (project scope).
            $t->foreignId('project_expense_id')->nullable()->constrained('project_expenses')->nullOnDelete();

            $t->timestamps();
            $t->index(['status', 'scope']);
            $t->index('requester_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spend_requests');
    }
};
