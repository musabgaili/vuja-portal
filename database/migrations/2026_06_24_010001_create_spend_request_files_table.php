<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Multiple receipts/attachments per spend request (a multi-item reimbursement
 * usually has several receipts). `kind`: receipt | actual_receipt | attachment.
 * Files live on the private disk; download is gated by the controller.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spend_request_files', function (Blueprint $t) {
            $t->id();
            $t->foreignId('spend_request_id')->constrained()->cascadeOnDelete();
            $t->string('kind')->default('receipt');
            $t->string('path');
            $t->string('original_name')->nullable();
            $t->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['spend_request_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spend_request_files');
    }
};
