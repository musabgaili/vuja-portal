<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Project management
            'view projects',
            'create projects',
            'edit projects',
            'delete projects',
            'assign projects',
            
            // Task management
            'view tasks',
            'create tasks',
            'edit tasks',
            'delete tasks',
            'assign tasks',
            
            // Client management
            'view clients',
            'create clients',
            'edit clients',
            'delete clients',
            
            // Manager permissions
            'view all projects',
            'view all tasks',
            'approve projects',
            'manage team',
            
            // Employee permissions
            'view assigned projects',
            'view assigned tasks',
            'update task status',
            
            // Client permissions
            'view own projects',
            'view own tasks',
            'request changes',
            'upload files',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $clientRole = Role::firstOrCreate(['name' => 'client']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $projectManagerRole = Role::firstOrCreate(['name' => 'project_manager']);

        // Assign permissions to roles
        $clientRole->givePermissionTo([
            'view own projects',
            'view own tasks',
            'request changes',
            'upload files',
        ]);

        $employeeRole->givePermissionTo([
            'view assigned projects',
            'view assigned tasks',
            'update task status',
            'view tasks',
            'edit tasks',
        ]);

        $projectManagerRole->givePermissionTo([
            'view projects',
            'create projects',
            'edit projects',
            'assign projects',
            'view tasks',
            'create tasks',
            'edit tasks',
            'assign tasks',
            'view assigned projects',
            'view assigned tasks',
            'update task status',
        ]);

        $managerRole->givePermissionTo([
            'view users',
            'create users',
            'edit users',
            'view all projects',
            'view all tasks',
            'create projects',
            'edit projects',
            'delete projects',
            'approve projects',
            'manage team',
            'view clients',
            'create clients',
            'edit clients',
        ]);
    }
}
