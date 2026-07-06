<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $models = [
            'Article', 'Testimony', 'Faq', 'HeroBanner', 'ProductBanner', 'ResellerBanner', 'Product',
            'Category', 'Event', 'Partner', 'User', 'Role',
            'Contact', 'SocialMedia', 'Collection',
        ];

        // 3. Create view and manage permissions in the database
        foreach ($models as $model) {
            Permission::findOrCreate("view {$model}");
            Permission::findOrCreate("manage {$model}");
        }

        // --- SIMPLIFIED PERMISSION HELPERS ---

        // Helper to get ALL permissions for a model
        $getFullAccess = fn ($model) => [
            "view {$model}",
            "manage {$model}",
        ];

        // 4. Create Roles
        Role::findOrCreate('Super Admin');
        $writerRole = Role::findOrCreate('Writer');

        // 5. Assign Permissions

        // Super Admin intentionally gets no permissions directly assigned because
        // it bypasses all checks via Gate::before in AppServiceProvider.

        // Writer gets Full Access to specific resources
        $writerPermissions = array_merge(
            $getFullAccess('Article'),
            $getFullAccess('Testimony'),
            $getFullAccess('Faq')
        );

        $writerRole->syncPermissions($writerPermissions);
    }
}
