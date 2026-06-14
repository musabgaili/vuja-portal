<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Link opportunities to the CRM address book (company + contact). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('client_id')->constrained('companies')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->after('company_id')->constrained('contacts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropConstrainedForeignKey('company_id');
            $table->dropConstrainedForeignKey('contact_id');
        });
    }
};
