<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test users with different roles
        
        // Client User
        $client = User::create([
            'name' => 'John Client',
            'email' => 'client@vujade.com',
            'phone' => '+1234567890',
            'password' => '12345678',
            'role' => UserRole::CLIENT,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);
        // $client->assignRole('client');

        // Employee User
        $employee = User::create([
            'name' => 'Sarah Employee',
            'email' => 'employee@vujade.com',
            'phone' => '+1234567891',
            'password' => '12345678',
            'role' => UserRole::EMPLOYEE,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);
        // $employee->assignRole('employee');

        // Manager User
        $manager = User::create([
            'name' => 'Mike Manager',
            'email' => 'manager@vujade.com',
            'phone' => '+1234567892',
            'password' => '12345678',
            'role' => UserRole::MANAGER,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);
        // $manager->assignRole('manager');

        // Project Manager User
        $projectManager = User::create([
            'name' => 'Lisa Project Manager',
            'email' => 'pm@vujade.com',
            'phone' => '+1234567893',
            'password' => '12345678',
            'role' => UserRole::MANAGER,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);
        // $projectManager->assignRole('project_manager');
    }
}