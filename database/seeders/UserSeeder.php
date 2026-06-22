<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hard refusal on production: these are DEMO accounts with the shared
        // password "12345678" and must never exist on a live host, even via a
        // direct `db:seed --class=UserSeeder` invocation.
        if (app()->environment('production')) {
            $this->command?->warn('UserSeeder skipped: demo login accounts are not seeded in production.');

            return;
        }

        // Create test users with different roles

        // Client User
        $client = User::create([
            'name' => 'John Client',
            'email' => 'client@vujade.com',
            'phone' => '+1234567890',
            'password' => '12345678',
            'role' => UserRole::CLIENT,
            'status' => UserStatus::ACTIVE,
            'type' => 'client',
            'email_verified_at' => now(),
        ]);
        $client->assignRole('client');

        // Employee User
        $employee = User::create([
            'name' => 'Sarah Employee',
            'email' => 'employee@vujade.com',
            'phone' => '+1234567891',
            'password' => '12345678',
            'role' => UserRole::EMPLOYEE,
            'status' => UserStatus::ACTIVE,
            'type' => 'internal',
            'email_verified_at' => now(),
        ]);
        $employee->assignRole('employee');

        // Manager User
        $manager = User::create([
            'name' => 'Mike Manager',
            'email' => 'manager@vujade.com',
            'phone' => '+1234567892',
            'password' => '12345678',
            'role' => UserRole::MANAGER,
            'status' => UserStatus::ACTIVE,
            'type' => 'internal',
            'email_verified_at' => now(),
        ]);
        $manager->assignRole('manager');

        // Project Manager User
        $projectManager = User::create([
            'name' => 'Lisa Project Manager',
            'email' => 'pm@vujade.com',
            'phone' => '+1234567893',
            'password' => '12345678',
            'role' => UserRole::PROJECT_MANAGER,
            'status' => UserStatus::ACTIVE,
            'type' => 'internal',
            'email_verified_at' => now(),
        ]);
        $projectManager->assignRole('project_manager');
    }
}
