<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        // Catalog / config seeders — idempotent (firstOrCreate) and safe to run on
        // every environment incl. production.
        $this->call([
            RolePermissionSeeder::class,
            ServiceRequestTypeSeeder::class,
            PricingRuleSeeder::class,
            InventoryItemSeeder::class,
            EngagementPointsSeeder::class,
            TargetMetricsSeeder::class,
            ActivityCategoriesSeeder::class,
        ]);

        // Demo LOGIN accounts + demo team roster + demo project data are NEVER
        // seeded in production: they ship the shared, well-known password
        // "12345678" (manager@vujade.com etc.) and would be a remote admin
        // backdoor. Real prod accounts come from registration / team invites.
        // (UserSeeder must precede TeamMembersSeeder / DemoDataSeeder.)
        if (! app()->environment('production')) {
            $this->call([
                UserSeeder::class,
                TeamMembersSeeder::class,
                DemoDataSeeder::class,
            ]);
        }
    }
}
