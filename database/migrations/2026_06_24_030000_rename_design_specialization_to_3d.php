<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The "design" specialisation became "3d_designer" (3D Designer). Re-point any
 * existing team members so they keep a valid specialisation under the new list.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('team_members')->where('specialization', 'design')->update(['specialization' => '3d_designer']);
    }

    public function down(): void
    {
        DB::table('team_members')->where('specialization', '3d_designer')->update(['specialization' => 'design']);
    }
};
