<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CRM sales pipeline. An opportunity (a.k.a. lead/deal) flows through stages and,
 * when won, converts into a delivery Project.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('name');                              // deal title
            $table->string('company_name')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('source')->nullable();                // website, referral, event...
            $table->string('stage')->default('new');             // new|qualified|proposition|won|lost
            $table->decimal('expected_value', 12, 2)->default(0);
            $table->unsignedTinyInteger('probability')->default(10);
            $table->date('expected_close_date')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();   // salesperson
            $table->foreignId('client_id')->nullable()->constrained('users')->nullOnDelete();  // if a registered client
            $table->text('description')->nullable();
            $table->string('lost_reason')->nullable();
            $table->timestamp('won_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->foreignId('converted_project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->timestamps();

            $table->index('stage');
            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
