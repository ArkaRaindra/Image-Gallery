<?php

namespace App\Filament\Resources\UserRoles\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserRoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Username')
                    ->disabled()
                    ->dehydrated(false),
                Select::make('role')
                    ->label('Role')
                    ->options(function (?User $record): array {
                        $roles = self::actor()->assignableRoles();

                        if ($record !== null) {
                            $roles[] = $record->role;
                        }

                        return User::roleOptions($roles);
                    })
                    ->required(),
            ]);
    }

    protected static function actor(): User
    {
         /** @var User $actor */
         $actor = auth()->user();

         return $actor;
    }
}
