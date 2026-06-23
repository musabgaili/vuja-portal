<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'prototype_requests'       => 'manager_notes',
            'three_d_requests'         => 'manager_notes',
            'ip_registrations'         => 'assigned_to',
            'copyright_registrations'  => 'assigned_to',
            'research_requests'        => 'assigned_to',
            'consultation_requests'    => 'assigned_to',
            'idea_requests'            => 'assigned_to',
        ];

        foreach ($tables as $table => $after) {
            Schema::table($table, function (Blueprint $t) use ($after) {
                $t->string('worker_status')->nullable()->after($after);
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'prototype_requests', 'three_d_requests', 'ip_registrations',
            'copyright_registrations', 'research_requests', 'consultation_requests',
            'idea_requests',
        ];

        foreach ($tables as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn('worker_status'));
        }
    }
};
