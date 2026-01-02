<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions
        $permissions = [
            // Conversations
            'view conversations',
            'manage conversations',
            'delete conversations',
            
            // Contacts
            'view contacts',
            'manage contacts',
            'delete contacts',
            
            // Companies
            'view companies',
            'manage companies',
            'delete companies',
            
            // Inboxes
            'view inboxes',
            'manage inboxes',
            'delete inboxes',
            
            // Team
            'view team',
            'manage team',
            
            // Settings
            'view settings',
            'manage settings',
            
            // Labels
            'view labels',
            'manage labels',
            
            // Canned responses
            'view canned responses',
            'manage canned responses',
            
            // Automations
            'view automations',
            'manage automations',
            
            // Reports
            'view reports',
            'manage reports',
            
            // Campaigns
            'view campaigns',
            'manage campaigns',
            
            // Macros
            'view macros',
            'manage macros',
            
            // Agent bots
            'view agent bots',
            'manage agent bots',
            
            // Webhooks
            'view webhooks',
            'manage webhooks',
            
            // Help center
            'view help center',
            'manage help center',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Agent role with limited permissions
        $agent = Role::firstOrCreate(['name' => 'agent']);
        $agent->syncPermissions([
            'view conversations',
            'manage conversations',
            'view contacts',
            'manage contacts',
            'view labels',
            'view canned responses',
            'manage canned responses',
            'view macros',
            'manage macros',
        ]);

        // Create Admin role with most permissions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'view conversations',
            'manage conversations',
            'delete conversations',
            'view contacts',
            'manage contacts',
            'delete contacts',
            'view companies',
            'manage companies',
            'view inboxes',
            'manage inboxes',
            'view team',
            'manage team',
            'view settings',
            'manage settings',
            'view labels',
            'manage labels',
            'view canned responses',
            'manage canned responses',
            'view automations',
            'manage automations',
            'view reports',
            'manage reports',
            'view campaigns',
            'manage campaigns',
            'view macros',
            'manage macros',
            'view agent bots',
            'manage agent bots',
            'view webhooks',
            'manage webhooks',
            'view help center',
            'manage help center',
        ]);

        // Create Super Admin role with all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all());
    }
}
