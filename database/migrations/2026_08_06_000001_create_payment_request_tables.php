<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('client_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->nullableMorphs('payable');
            $table->string('name');
            $table->string('email')->index();
            $table->string('phone', 40)->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('unit_amount_minor');
            $table->unsignedBigInteger('total_amount_minor');
            $table->string('currency', 3)->default('SAR');
            $table->string('tax_id', 32)->nullable();
            $table->text('billing_address')->nullable();
            $table->string('status', 24)->default('pending')->index();
            $table->timestamp('expires_at')->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_request_id')->constrained()->cascadeOnDelete();
            $table->uuid('moyasar_payment_id')->unique();
            $table->string('status', 24)->default('initiated')->index();
            $table->unsignedBigInteger('amount_minor');
            $table->string('currency', 3);
            $table->timestamp('provider_created_at')->nullable();
            $table->timestamp('provider_updated_at')->nullable();
            $table->json('provider_data')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_request_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_attempt_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source', 24);
            $table->uuid('provider_event_id')->nullable()->unique();
            $table->string('event_type', 64)->index();
            $table->timestamp('provider_occurred_at')->nullable();
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->string('outcome', 32)->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->index(['payment_request_id', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_request_events');
        Schema::dropIfExists('payment_attempts');
        Schema::dropIfExists('payment_requests');
    }
};
