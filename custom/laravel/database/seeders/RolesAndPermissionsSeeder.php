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

        // Create permissions for conversations
        Permission::create(['name' => 'view conversations']);
        Permission::create(['name' => 'manage conversations']);
        Permission::create(['name' => 'delete conversations']);

        // Create permissions for contacts
        Permission::create(['name' => 'view contacts']);
        Permission::create(['name' => 'manage contacts']);
        Permission::create(['name' => 'delete contacts']);

        // Create permissions for inboxes
        Permission::create(['name' => 'view inboxes']);
        Permission::create(['name' => 'manage inboxes']);
        Permission::create(['name' => 'delete inboxes']);

        // Create permissions for team
        Permission::create(['name' => 'view team']);
        Permission::create(['name' => 'manage team']);

        // Create permissions for settings
        Permission::create(['name' => 'view settings']);
        Permission::create(['name' => 'manage settings']);

        // Create permissions for labels
        Permission::create(['name' => 'view labels']);
        Permission::create(['name' => 'manage labels']);

        // Create permissions for canned responses
        Permission::create(['name' => 'view canned responses']);
        Permission::create(['name' => 'manage canned responses']);

        // Create permissions for automations
        Permission::create(['name' => 'view automations']);
        Permission::create(['name' => 'manage automations']);

        // Create permissions for reports
        Permission::create(['name' => 'view reports']);
        Permission::create(['name' => 'manage reports']);

        // Create permissions for campaigns
        Permission::create(['name' => 'view campaigns']);
        Permission::create(['name' => 'manage campaigns']);

        // Create permissions for macros
        Permission::create(['name' => 'view macros']);
        Permission::create(['name' => 'manage macros']);

        // Create permissions for agent bots
        Permission::create(['name' => 'view agent bots']);
        Permission::create(['name' => 'manage agent bots']);

        // Create permissions for webhooks
        Permission::create(['name' => 'view webhooks']);
        Permission::create(['name' => 'manage webhooks']);

        // Create permissions for help center
        Permission::create(['name' => 'view help center']);
        Permission::create(['name' => 'manage help center']);

        // Create Agent role with limited permissions
        $agent = Role::create(['name' => 'agent']);
        $agent->givePermissionTo([
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
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'view conversations',
            'manage conversations',
            'delete conversations',
            'view contacts',
            'manage contacts',
            'delete contacts',
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
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());
    }
}
