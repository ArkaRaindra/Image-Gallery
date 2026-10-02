<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Permissions;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can(Permissions::VIEW_ANY_USER);
    }

    public function view(User $actor, User $target): bool
    {
        return $actor->can(Permissions::VIEW_USER);
    }

    public function create(User $actor): bool
    {
        return $actor->can(Permissions::CREATE_USER);
    }

    public function update(User $actor, User $target): bool
    {
        return $actor->can(Permissions::UPDATE_USER) && $actor->canManage($target);
    }

    public function delete(User $actor, User $target): bool
    {
        return $actor->can(Permissions::DELETE_USER) && $actor->outranks($target);
    }

    public function deleteAny(User $actor): bool
    {
        return $actor->can(Permissions::DELETE_ANY_USER);
    }
}
