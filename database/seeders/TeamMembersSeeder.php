<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds a team_member (engineer) row for each active internal employee at the
 * default 40h/week capacity. Idempotent. The manager confirms specialization +
 * the skill map in the Engineers config screen (spec §17 — seeded so non-blocking).
 */
class TeamMembersSeeder extends Seeder
{
    public function run(): void
    {
        $employees = User::where('type', 'internal')
            ->where('role', 'employee')
            ->where('status', 'active')
            ->get();

        foreach ($employees as $user) {
            TeamMember::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'display_name' => $user->name,
                    'weekly_capacity_hours' => (float) config('targets.default_weekly_capacity', 40),
                    'is_active' => true,
                ],
            );
        }
    }
}
