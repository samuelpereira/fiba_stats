<?php

namespace Database\Seeders;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Create the application roles and the permissions they grant.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionName::cases() as $permission) {
            Permission::findOrCreate($permission->value, 'web');
        }

        Role::findOrCreate(RoleName::Administrator->value, 'web')->syncPermissions([
            PermissionName::CreateClubs,
            PermissionName::CreateUsers,
        ]);

        Role::findOrCreate(RoleName::Manager->value, 'web')->syncPermissions([
            PermissionName::CreateClubs,
            PermissionName::CreateUsers,
        ]);

        Role::findOrCreate(RoleName::Club->value, 'web')->syncPermissions([
            PermissionName::CreateUsers,
        ]);

        Role::findOrCreate(RoleName::User->value, 'web')->syncPermissions([]);
    }
}
