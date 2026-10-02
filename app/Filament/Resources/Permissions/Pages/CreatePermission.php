<?php

namespace App\Filament\Resources\Permissions\Pages;

use App\Filament\Resources\Permissions\PermissionResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreatePermission extends CreateRecord
{
    protected static string $resource = PermissionResource::class;

    /**
     * The owner role always holds every permission, including new ones.
     */
    protected function afterCreate(): void
    {
        /** @var Permission $permission */
        $permission = $this->record;

        Role::query()
            ->where('name', User::ROLE_OWNER)
            ->where('guard_name', $permission->guard_name)
            ->first()
            ?->givePermissionTo($permission);
    }
}
