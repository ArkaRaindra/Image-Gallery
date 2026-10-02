<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Support\Permissions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Role')
                    ->formatStateUsing(fn (?string $state): string => ucfirst((string) $state))
                    ->disabled()
                    ->dehydrated(false),
                CheckboxList::make('permissions')
                    ->label('Permissions')
                    ->options(fn (): array => self::assignablePermissions())
                    ->afterStateHydrated(function (CheckboxList $component, ?Role $record): void {
                        $held = $record?->permissions->pluck('name')->all() ?? [];

                        $component->state(array_values(array_intersect($held, array_keys(self::assignablePermissions()))));
                    })
                    ->columns(2)
                    ->bulkToggleable()
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Permissions that may be given to a role (name => label). Permissions
     * reserved for the owner are left out.
     *
     * @return array<string, string>
     */
    protected static function assignablePermissions(): array
    {
        return Permission::query()
            ->whereNotIn('name', Permissions::ownerOnly())
            ->orderBy('name')
            ->pluck('name')
            ->mapWithKeys(fn (string $name): array => [$name => Str::headline($name)])
            ->all();
    }
}
