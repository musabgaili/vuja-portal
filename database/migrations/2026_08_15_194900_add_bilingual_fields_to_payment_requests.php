<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->string('title_en', 180)->nullable()->after('title');
            $table->string('title_ar', 180)->nullable()->after('title_en');
            $table->text('description_en')->nullable()->after('description');
            $table->text('description_ar')->nullable()->after('description_en');
        });

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::table('payment_requests')->orderBy('id')->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('payment_requests')->where('id', $row->id)->update([
                        'title_en' => $row->title,
                        'title_ar' => $row->title,
                        'description_en' => $row->description,
                        'description_ar' => $row->description,
                    ]);
                }
            });
        } else {
            DB::table('payment_requests')->get()->each(function ($row) {
                DB::table('payment_requests')->where('id', $row->id)->update([
                    'title_en' => $row->title,
                    'title_ar' => $row->title,
                    'description_en' => $row->description,
                    'description_ar' => $row->description,
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'title_ar', 'description_en', 'description_ar']);
        });
    }
};
