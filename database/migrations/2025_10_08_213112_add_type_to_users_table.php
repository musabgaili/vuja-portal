<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('type', ['client', 'internal'])->default('client')->after('role');
        });
        
        // Update existing users based on their role
        DB::table('users')->where('role', 'client')->update(['type' => 'client']);
        DB::table('users')->whereIn('role', ['employee', 'manager'])->update(['type' => 'internal']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};