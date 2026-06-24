<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records when each assignable service request reached its terminal "completed"
 * state, so the "services completed" target metric can count deliveries per
 * month and the points gate can award the assignee impact points beyond target.
 */
return new class extends Migration
{
    private array $tables = [
        'idea_requests',
        'consultation_requests',
        'research_requests',
        'ip_registrations',
        'copyright_registrations',
        'three_d_requests',
        'prototype_requests',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'service_completed_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->timestamp('service_completed_at')->nullable()->index();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'service_completed_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('service_completed_at');
                });
            }
        }
    }
};
