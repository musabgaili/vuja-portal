<?php

namespace Database\Seeders;

use App\Models\EarningRule;
use App\Models\RedemptionOption;
use App\Models\Tier;
use Illuminate\Database\Seeder;

/**
 * Seeds the launch defaults for the Client Engagement Points program (spec §7,
 * §9, §12). Idempotent: firstOrCreate keyed by `key`, so re-running never
 * clobbers values an admin has since tuned in the admin module.
 */
class EngagementPointsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->earningRules() as $i => $r) {
            EarningRule::firstOrCreate(['key' => $r['key']], array_merge($r, ['sort_order' => $i + 1]));
        }

        foreach ($this->redemptionOptions() as $i => $o) {
            RedemptionOption::firstOrCreate(['key' => $o['key']], array_merge($o, ['sort_order' => $i + 1]));
        }

        foreach ($this->tiers() as $i => $t) {
            Tier::firstOrCreate(['key' => $t['key']], array_merge($t, ['sort_order' => $i + 1]));
        }
    }

    /** Earning catalog (§7). auto_award=false ⇒ claim/manual. */
    private function earningRules(): array
    {
        return [
            ['key' => 'idea_accepted', 'name' => 'Accepted innovation idea', 'name_ar' => 'فكرة ابتكارية مقبولة', 'points' => 10, 'is_active' => true, 'auto_award' => true, 'requires_review' => false, 'cap_count' => 5, 'cap_period' => 'lifetime'],
            ['key' => 'referral_signup', 'name' => 'Referral signed up', 'name_ar' => 'تسجيل عميل عبر الإحالة', 'points' => 10, 'is_active' => true, 'auto_award' => true, 'requires_review' => false, 'cap_count' => null, 'cap_period' => null],
            ['key' => 'referral_payment_small', 'name' => 'Referral first payment (standard)', 'name_ar' => 'أول دفعة لعميل محال (قياسي)', 'points' => 50, 'is_active' => true, 'auto_award' => true, 'requires_review' => false, 'cap_count' => null, 'cap_period' => null],
            ['key' => 'referral_payment_large', 'name' => 'Referral first payment (major)', 'name_ar' => 'أول دفعة لعميل محال (كبير)', 'points' => 100, 'is_active' => true, 'auto_award' => true, 'requires_review' => false, 'cap_count' => null, 'cap_period' => null],
            ['key' => 'profile_complete', 'name' => 'Completed your profile', 'name_ar' => 'إكمال الملف الشخصي', 'points' => 10, 'is_active' => true, 'auto_award' => true, 'requires_review' => false, 'cap_count' => 1, 'cap_period' => 'lifetime'],
            // Claim rules (auto_award=false): the manual review queue IS the review,
            // so requires_review stays false — it only gates auto-awards.
            ['key' => 'event_attendance', 'name' => 'Attended a Vuja De event', 'name_ar' => 'حضور فعالية فوجا دي', 'points' => 40, 'is_active' => true, 'auto_award' => false, 'requires_review' => false, 'cap_count' => null, 'cap_period' => null],
            ['key' => 'social_follow', 'name' => 'Followed us on social media', 'name_ar' => 'متابعتنا على وسائل التواصل', 'points' => 5, 'is_active' => true, 'auto_award' => false, 'requires_review' => false, 'cap_count' => 1, 'cap_period' => 'lifetime'],
            ['key' => 'social_story', 'name' => 'Shared a story / reel', 'name_ar' => 'مشاركة قصة / ريل', 'points' => 15, 'is_active' => true, 'auto_award' => false, 'requires_review' => false, 'cap_count' => 2, 'cap_period' => 'month'],
            ['key' => 'social_post', 'name' => 'Published a post about us', 'name_ar' => 'نشر منشور عنّا', 'points' => 40, 'is_active' => true, 'auto_award' => false, 'requires_review' => false, 'cap_count' => 2, 'cap_period' => 'month'],
            ['key' => 'review_public', 'name' => 'Public review / recommendation', 'name_ar' => 'تقييم عام / توصية', 'points' => 90, 'is_active' => true, 'auto_award' => false, 'requires_review' => false, 'cap_count' => 1, 'cap_period' => 'quarter'],
            ['key' => 'video_testimonial', 'name' => 'Video testimonial', 'name_ar' => 'شهادة مرئية', 'points' => 250, 'is_active' => true, 'auto_award' => false, 'requires_review' => false, 'cap_count' => null, 'cap_period' => null],
            ['key' => 'idea_bonus_implemented', 'name' => 'Idea shipped to production (bonus)', 'name_ar' => 'تطبيق الفكرة فعلياً (مكافأة)', 'points' => 50, 'is_active' => true, 'auto_award' => false, 'requires_review' => false, 'cap_count' => null, 'cap_period' => null],
            ['key' => 'survey_nps', 'name' => 'Completed an NPS survey', 'name_ar' => 'إكمال استبيان NPS', 'points' => 10, 'is_active' => false, 'auto_award' => true, 'requires_review' => false, 'cap_count' => 1, 'cap_period' => 'quarter'],
        ];
    }

    /** Redemption catalog (§9). */
    private function redemptionOptions(): array
    {
        $cap = (float) config('engagement_points.discount_cap_sar', 2500);

        return [
            ['key' => 'discount_5', 'name' => '5% service discount', 'name_ar' => 'خصم 5% على الخدمات', 'type' => 'service_discount', 'cost_points' => 100, 'value_meta' => ['percent' => 5, 'cap_sar' => $cap], 'is_active' => true],
            ['key' => 'discount_10', 'name' => '10% service discount', 'name_ar' => 'خصم 10% على الخدمات', 'type' => 'service_discount', 'cost_points' => 200, 'value_meta' => ['percent' => 10, 'cap_sar' => $cap], 'is_active' => true],
            ['key' => 'discount_15', 'name' => '15% service discount', 'name_ar' => 'خصم 15% على الخدمات', 'type' => 'service_discount', 'cost_points' => 300, 'value_meta' => ['percent' => 15, 'cap_sar' => $cap], 'is_active' => true],
            ['key' => 'ai_run', 'name' => 'AI idea-engine — one run', 'name_ar' => 'محرك الأفكار الذكي — تشغيلة واحدة', 'type' => 'ai_access', 'cost_points' => 25, 'value_meta' => ['mode' => 'run'], 'is_active' => true],
            ['key' => 'ai_pass_30d', 'name' => 'AI idea-engine — 30-day pass', 'name_ar' => 'محرك الأفكار الذكي — اشتراك 30 يوماً', 'type' => 'ai_access', 'cost_points' => 120, 'value_meta' => ['mode' => 'pass', 'days' => 30], 'is_active' => true],
            ['key' => 'free_consultation', 'name' => 'Free 60-min consultation', 'name_ar' => 'استشارة مجانية 60 دقيقة', 'type' => 'consultation', 'cost_points' => 350, 'value_meta' => ['minutes' => 60], 'is_active' => true],
        ];
    }

    /** Status tiers (§12). */
    private function tiers(): array
    {
        return [
            ['key' => 'explorer', 'name' => 'Explorer', 'name_ar' => 'مستكشف', 'min_lifetime_points' => 0, 'badge' => 'compass', 'perks' => ['en' => ['Base access'], 'ar' => ['وصول أساسي']]],
            ['key' => 'innovator', 'name' => 'Innovator', 'name_ar' => 'مبتكر', 'min_lifetime_points' => 200, 'badge' => 'lightbulb', 'perks' => ['en' => ['Featured idea', 'AI-engine trial pass'], 'ar' => ['فكرة مميّزة', 'تجربة محرك الأفكار']]],
            ['key' => 'pioneer', 'name' => 'Pioneer', 'name_ar' => 'رائد', 'min_lifetime_points' => 600, 'badge' => 'rocket', 'perks' => ['en' => ['Priority scheduling', 'Event invites'], 'ar' => ['أولوية الجدولة', 'دعوات الفعاليات']]],
            ['key' => 'partner', 'name' => 'Partner', 'name_ar' => 'شريك', 'min_lifetime_points' => 1500, 'badge' => 'crown', 'perks' => ['en' => ['Top perks', 'Advisory invites', 'Case-study feature', 'Dedicated contact'], 'ar' => ['أفضل المزايا', 'دعوات استشارية', 'عرض دراسة حالة', 'مسؤول مخصص']]],
        ];
    }
}
