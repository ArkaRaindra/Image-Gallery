<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Permissions;
use Spatie\Permission\Models\Permission;

/**
 * System permissions are checked by the code, so only custom permissions can
 * be renamed or deleted.
 */
class PermissionPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can(Permissions::VIEW_ANY_PERMISSION);
    }

    public function create(User $actor): bool
    {
        return $actor->can(Permissions::CREATE_PERMISSION);
    }

    public function update(User $actor, Permission $permission): bool
    {
        return $actor->can(Permissions::UPDATE_PERMISSION) && ! Permissions::isSystem($permission->name);
    }

    public function delete(User $actor, Permission $permission): bool
    {
        return $actor->can(Permissions::DELETE_PERMISSION) && ! Permissions::isSystem($permission->name);
    }

    public function deleteAny(User $actor): bool
    {
        return $actor->can(Permissions::DELETE_ANY_PERMISSION);
    }
}
