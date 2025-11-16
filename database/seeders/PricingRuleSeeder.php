<?php

namespace Database\Seeders;

use App\Models\PricingRule;
use Illuminate\Database\Seeder;

class PricingRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'item' => '3D Design',
                'rate' => 100.00,
                'unit' => 'hour',
                'level' => 'beginner',
                'note' => 'Basic model quality, no complex rendering.',
                'is_active' => true,
            ],
            [
                'item' => '3D Design',
                'rate' => 150.00,
                'unit' => 'hour',
                'level' => 'medium',
                'note' => 'Standard complexity, light texture work included.',
                'is_active' => true,
            ],
            [
                'item' => '3D Design',
                'rate' => 200.00,
                'unit' => 'hour',
                'level' => 'expert',
                'note' => 'High-fidelity models, animation ready.',
                'is_active' => true,
            ],
            [
                'item' => '3D Printing',
                'rate' => 50.00,
                'unit' => 'piece',
                'level' => 'standard',
                'note' => 'Standard material and quality.',
                'is_active' => true,
            ],
            [
                'item' => '3D Printing',
                'rate' => 75.00,
                'unit' => 'piece',
                'level' => 'complex',
                'note' => 'Requires support structures and specific material.',
                'is_active' => true,
            ],
            [
                'item' => 'Web Development',
                'rate' => 80.00,
                'unit' => 'hour',
                'level' => 'junior',
                'note' => 'Basic frontend/backend development.',
                'is_active' => true,
            ],
            [
                'item' => 'Web Development',
                'rate' => 120.00,
                'unit' => 'hour',
                'level' => 'senior',
                'note' => 'Complex architecture, API design, optimization.',
                'is_active' => true,
            ],
            [
                'item' => 'UI/UX Design',
                'rate' => 90.00,
                'unit' => 'screen',
                'level' => 'standard',
                'note' => 'Wireframes and basic mockups.',
                'is_active' => true,
            ],
            [
                'item' => 'UI/UX Design',
                'rate' => 150.00,
                'unit' => 'screen',
                'level' => 'premium',
                'note' => 'High-fidelity designs with animations and prototypes.',
                'is_active' => true,
            ],
            [
                'item' => 'Consultation',
                'rate' => 200.00,
                'unit' => 'hour',
                'level' => 'expert',
                'note' => 'Strategic consulting and business analysis.',
                'is_active' => true,
            ],
        ];

        foreach ($rules as $rule) {
            PricingRule::create($rule);
        }
    }
}
