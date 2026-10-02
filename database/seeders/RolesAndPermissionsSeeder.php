<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates the four roles and every system permission, and links each existing
 * user to the Spatie role that matches their role column.
 *
 * Safe to run again: the owner always gets every permission, while the admin
 * role only receives its defaults when it has no permissions at all, so edits
 * made in the panel are kept.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Permissions::all() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (array_keys(User::ROLE_LEVELS) as $roleName) {
            $role = Role::findOrCreate($roleName, 'web');

            if ($roleName === User::ROLE_OWNER) {
                $role->syncPermissions(Permission::query()->where('guard_name', 'web')->get());

                continue;
            }

            if ($roleName === User::ROLE_ADMIN && $role->permissions()->doesntExist()) {
                $role->givePermissionTo(Permissions::adminDefaults());
            }
        }

        User::query()->each(fn (User $user) => $user->syncSpatieRole());
    }
}
