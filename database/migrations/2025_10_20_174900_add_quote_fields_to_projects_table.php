<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('quoted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('quote_file')->nullable();
            $table->timestamp('quoted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['quoted_by']);
            $table->dropColumn(['quoted_by', 'quote_file', 'quoted_at']);
        });
    }
};
