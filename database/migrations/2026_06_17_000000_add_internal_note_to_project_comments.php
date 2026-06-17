<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_comments', function (Blueprint $table) {
            // Internal team note: hidden from the client. Distinct from is_internal
            // (which only records that the author is staff — those stay client-visible).
            if (! Schema::hasColumn('project_comments', 'internal_note')) {
                $table->boolean('internal_note')->default(false)->after('is_internal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('project_comments', function (Blueprint $table) {
            if (Schema::hasColumn('project_comments', 'internal_note')) {
                $table->dropColumn('internal_note');
            }
        });
    }
};
