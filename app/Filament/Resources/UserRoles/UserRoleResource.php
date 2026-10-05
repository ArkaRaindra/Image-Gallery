<?php

namespace App\Filament\Resources\UserRoles;

use App\Filament\Resources\UserRoles\Pages\CreateUserRole;
use App\Filament\Resources\UserRoles\Pages\EditUserRole;
use App\Filament\Resources\UserRoles\Pages\ListUserRoles;
use App\Filament\Resources\UserRoles\Schemas\UserRoleForm;
use App\Filament\Resources\UserRoles\Tables\UserRolesTable;
use App\Models\User;
use App\Support\Permissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Access\Response;
use Override;
use UnitEnum;

class UserRoleResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'user-roles';

    protected static ?string $navigationLabel = 'User Roles';

    protected static ?string $modelLabel = 'user role';

    protected static ?string $pluralModelLabel = 'user roles';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Identification;

    protected static string|UnitEnum|null $navigationGroup = 'Master';

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $isGloballySearchable = false;

    #[Override]
    public static function getAuthorizationResponse(string|UnitEnum $action, ?Model $record = null): Response
    {
        /** @var User|null $actor */
        $actor = auth()->user();

        return match (true) {
            $actor === null => Response::deny(),
            $action === 'viewAny' => self::allowIf($actor->can(Permissions::VIEW_ANY_USER_ROLE)),
            $action === 'update' && $record instanceof User => self::allowIf(
                $actor->can(Permissions::UPDATE_USER_ROLE) && $actor->outranks($record),
            ),
            default => Response::deny(),
        };
    }

    public static function form(Schema $schema): Schema
    {
        return UserRoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserRolesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserRoles::route('/'),
            'edit' => EditUserRole::route('/{record}/edit'),
        ];
    }

    protected static function allowIf(bool $condition): Response
    {
        return $condition ? Response::allow() : Response::deny();
    }
}
