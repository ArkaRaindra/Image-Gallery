<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isAdmin();
    }

    public function view(User $actor, User $target): bool
    {
        return $actor->isAdmin();
    }

    public function create(User $actor): bool
    {
        return $actor->isAdmin();
    }

    public function update(User $actor, User $target): bool
    {
        return $actor->isAdmin() && $actor->canManage($target);
    }

    public function delete(User $actor, User $target): bool
    {
        return $actor->isAdmin() && $actor->outranks($target);
    }

    public function deleteAny(User $actor): bool
    {
        return $actor->isAdmin();
    }
}