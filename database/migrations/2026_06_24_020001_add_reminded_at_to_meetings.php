<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Stamps when the pre-meeting reminder was sent, so it goes out only once. */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('meetings', 'reminded_at')) {
            Schema::table('meetings', function (Blueprint $t) {
                $t->timestamp('reminded_at')->nullable()->after('completed_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('meetings', 'reminded_at')) {
            Schema::table('meetings', function (Blueprint $t) {
                $t->dropColumn('reminded_at');
            });
        }
    }
};
