<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support ENUM changes, so we'll just document the new statuses
        // The validation will be done in the controller
        // New statuses: planning, quoted, awarded, in_progress, paused, completed, lost, cancelled
    }

    public function down(): void
    {
        //
    }
};
