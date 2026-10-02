<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Permissions;
use Spatie\Permission\Models\Role;

/**
 * The four roles are fixed (they carry the rank used across the site), so
 * they can be listed and have their permissions edited, but never created,
 * renamed or deleted. The owner role always keeps every permission.
 */
class RolePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can(Permissions::VIEW_ANY_ROLE);
    }

    public function update(User $actor, Role $role): bool
    {
        return $actor->can(Permissions::UPDATE_ROLE) && $role->name !== User::ROLE_OWNER;
    }

    public function create(User $actor): bool
    {
        return false;
    }

    public function delete(User $actor, Role $role): bool
    {
        return false;
    }

    public function deleteAny(User $actor): bool
    {
        return false;
    }
}
