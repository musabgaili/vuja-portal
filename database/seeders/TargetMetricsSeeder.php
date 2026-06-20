<?php

namespace Database\Seeders;

use App\Models\TargetMetric;
use Illuminate\Database\Seeder;

/**
 * Seeds the auto-measured metric catalog (idempotent). Each maps to the
 * impact-points action it gates. Managers can add manual KPIs from the UI.
 */
class TargetMetricsSeeder extends Seeder
{
    public function run(): void
    {
        $metrics = [
            ['key' => 'quotes_produced', 'name' => 'Quotes produced', 'name_ar' => 'عروض الأسعار المُنتجة', 'source' => 'auto', 'unit' => 'count', 'engagement_action' => 'quote_produced'],
            ['key' => 'projects_won', 'name' => 'Projects won', 'name_ar' => 'المشاريع المكتسبة', 'source' => 'auto', 'unit' => 'count', 'engagement_action' => 'project_won'],
            ['key' => 'meetings_attended', 'name' => 'Meetings attended', 'name_ar' => 'الاجتماعات المحضورة', 'source' => 'auto', 'unit' => 'count', 'engagement_action' => 'meeting_attended'],
            ['key' => 'projects_closed', 'name' => 'Projects closed', 'name_ar' => 'المشاريع المُنجزة', 'source' => 'auto', 'unit' => 'count', 'engagement_action' => 'project_closed'],
            ['key' => 'presale_meeting_hours', 'name' => 'Pre-sale meeting hours', 'name_ar' => 'ساعات اجتماعات ما قبل البيع', 'source' => 'auto', 'unit' => 'hours', 'engagement_action' => null],
        ];

        foreach ($metrics as $i => $m) {
            TargetMetric::firstOrCreate(['key' => $m['key']], array_merge($m, ['is_active' => true, 'sort_order' => $i + 1]));
        }
    }
}
