<?php

namespace Database\Seeders;

use App\Models\ActivityCategory;
use Illuminate\Database\Seeder;

/** Seeds the 7 activity categories (spec §7). Idempotent; editable in the UI. */
class ActivityCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['key' => 'printing_3d', 'name' => '3D Printing', 'name_ar' => 'الطباعة ثلاثية الأبعاد', 'kind' => 'delivery', 'is_billable' => true],
            ['key' => 'electronic_design', 'name' => 'Electronic Design', 'name_ar' => 'التصميم الإلكتروني', 'kind' => 'delivery', 'is_billable' => true],
            ['key' => 'software_dev', 'name' => 'Software Development', 'name_ar' => 'تطوير البرمجيات', 'kind' => 'delivery', 'is_billable' => true],
            ['key' => 'prototyping', 'name' => 'Prototyping', 'name_ar' => 'النمذجة الأولية', 'kind' => 'delivery', 'is_billable' => true],
            ['key' => 'pre_sales', 'name' => 'Pre-Sales (meetings + SoW/quotes)', 'name_ar' => 'ما قبل البيع (اجتماعات + عروض)', 'kind' => 'business_dev', 'is_billable' => false],
            ['key' => 'internal', 'name' => 'Internal / Admin', 'name_ar' => 'داخلي / إداري', 'kind' => 'internal', 'is_billable' => false],
            ['key' => 'sales', 'name' => 'Sales (separate staff)', 'name_ar' => 'المبيعات (فريق منفصل)', 'kind' => 'business_dev', 'is_billable' => false],
        ];

        foreach ($categories as $i => $c) {
            ActivityCategory::firstOrCreate(['key' => $c['key']], array_merge($c, ['is_active' => true, 'sort_order' => $i + 1]));
        }
    }
}
