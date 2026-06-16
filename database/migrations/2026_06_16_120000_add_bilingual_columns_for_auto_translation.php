<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** table => [source text fields that get _en / _ar siblings] */
    private array $map = [
        'projects'               => ['title', 'description'],
        'project_milestones'     => ['title', 'description'],
        'project_tasks'          => ['title', 'description'],
        'idea_requests'          => ['title', 'description'],
        'consultation_requests'  => ['title', 'description'],
        'research_requests'      => ['title', 'research_topic', 'research_details'],
        'ip_registrations'       => ['title', 'ip_description'],
        'copyright_registrations'=> ['title', 'work_description'],
        'prototype_requests'     => ['title', 'description'],
    ];

    public function up(): void
    {
        foreach ($this->map as $table => $fields) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            Schema::table($table, function (Blueprint $t) use ($table, $fields) {
                foreach ($fields as $f) {
                    if (Schema::hasColumn($table, $f) && ! Schema::hasColumn($table, $f.'_en')) {
                        $t->text($f.'_en')->nullable()->after($f);
                        $t->text($f.'_ar')->nullable()->after($f.'_en');
                    }
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->map as $table => $fields) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            Schema::table($table, function (Blueprint $t) use ($table, $fields) {
                foreach ($fields as $f) {
                    foreach ([$f.'_en', $f.'_ar'] as $col) {
                        if (Schema::hasColumn($table, $col)) {
                            $t->dropColumn($col);
                        }
                    }
                }
            });
        }
    }
};
