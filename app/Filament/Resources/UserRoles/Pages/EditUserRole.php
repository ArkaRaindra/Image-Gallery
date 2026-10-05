<?php

namespace App\Filament\Resources\UserRoles\Pages;

use App\Filament\Resources\UserRoles\UserRoleResource;
use Filament\Resources\Pages\EditRecord;
use Override;

class EditUserRole extends EditRecord
{
    protected static string $resource = UserRoleResource::class;

    #[Override]
    protected function getRedirectUrl(): ?string
    {
        return static::getResource()::getUrl('index');
    }
}
