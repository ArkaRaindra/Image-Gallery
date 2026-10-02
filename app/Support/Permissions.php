<?php

namespace App\Support;

class Permissions
{
    public const ACCESS_ADMIN_PANEL = 'access_admin_panel';

    public const VIEW_ANY_USER = 'view_any_user';

    public const VIEW_USER = 'view_user';

    public const CREATE_USER = 'create_user';

    public const UPDATE_USER = 'update_user';

    public const DELETE_USER = 'delete_user';

    public const DELETE_ANY_USER = 'delete_any_user';

    public const VIEW_ANY_ROLE = 'view_any_role';

    public const UPDATE_ROLE = 'update_role';

    public const VIEW_ANY_PERMISSION = 'view_any_permission';

    public const CREATE_PERMISSION = 'create_permission';

    public const UPDATE_PERMISSION = 'update_permission';

    public const DELETE_PERMISSION = 'delete_permission';

    public const DELETE_ANY_PERMISSION = 'delete_any_permission';

    /**
     * Every permission the application itself checks.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::ACCESS_ADMIN_PANEL,
            self::VIEW_ANY_USER,
            self::VIEW_USER,
            self::CREATE_USER,
            self::UPDATE_USER,
            self::DELETE_USER,
            self::DELETE_ANY_USER,
            self::VIEW_ANY_ROLE,
            self::UPDATE_ROLE,
            self::VIEW_ANY_PERMISSION,
            self::CREATE_PERMISSION,
            self::UPDATE_PERMISSION,
            self::DELETE_PERMISSION,
            self::DELETE_ANY_PERMISSION,
        ];
    }

    /**
     * Permissions that only the owner holds. The role form never offers them,
     * so they cannot be handed to the admin, moderator or member role.
     *
     * @return list<string>
     */
    public static function ownerOnly(): array
    {
        return array_values(array_diff(self::all(), self::adminDefaults()));
    }

    /**
     * Permissions the admin role starts with.
     *
     * @return list<string>
     */
    public static function adminDefaults(): array
    {
        return [self::ACCESS_ADMIN_PANEL];
    }

    /**
     * System permissions are referenced by the code, so they cannot be renamed or deleted.
     */
    public static function isSystem(string $name): bool
    {
        return in_array($name, self::all(), true);
    }
}
