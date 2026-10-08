<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates one account per role, using the format role@gmail.com with the
 * password "password" (owner@gmail.com, admin@gmail.com, moderator@gmail.com,
 * member@gmail.com).
 *
 * Safe to run again: accounts are matched by email, so nothing is duplicated
 * and the password and role of these four accounts are reset to the defaults.
 *
 * This only sets the role column. The Spatie role is linked by
 * RolesAndPermissionsSeeder, which must run after this seeder.
 */
class AccountSeeder extends Seeder
{
    public function run(): void
    {
        foreach (array_keys(User::ROLE_LEVELS) as $role) {
            if ($role === User::ROLE_OWNER && $this->anotherOwnerExists($role.'@gmail.com')) {
                $this->command?->warn('Skipped owner@gmail.com: the app allows only one owner and one already exists.');

                continue;
            }

            User::query()->updateOrCreate(
                ['email' => $role.'@gmail.com'],
                [
                    'name' => ucfirst($role),
                    'password' => 'password',
                    'role' => $role,
                    'email_verified_at' => now(),
                ],
            );
        }
    }

    private function anotherOwnerExists(string $email): bool
    {
        return User::query()
            ->where('role', User::ROLE_OWNER)
            ->where('email', '!=', $email)
            ->exists();
    }
}