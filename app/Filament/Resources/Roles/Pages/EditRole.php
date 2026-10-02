<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use App\Support\Permissions;
use Filament\Resources\Pages\EditRecord;
use Override;
use Spatie\Permission\Models\Role;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    /**
     * @var list<string>
     */
    protected array $permissionNames = [];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[Override]
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->permissionNames = $data['permissions'] ?? [];

        unset($data['permissions']);

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var Role $role */
        $role = $this->record;

        $role->syncPermissions(array_values(array_diff($this->permissionNames, Permissions::ownerOnly())));
    }
}
