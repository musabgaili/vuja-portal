<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['client', 'employee', 'manager', 'project_manager'])->default('client')->after('phone');
            $table->enum('status', ['pending', 'active', 'suspended', 'inactive'])->default('pending')->after('role');
            $table->string('provider')->nullable()->after('status');
            $table->string('provider_id')->nullable()->after('provider');
            $table->timestamp('otp_verified_at')->nullable()->after('email_verified_at');
            $table->string('otp_code')->nullable()->after('otp_verified_at');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'role',
                'status',
                'provider',
                'provider_id',
                'otp_verified_at',
                'otp_code',
                'otp_expires_at'
            ]);
        });
    }
};
