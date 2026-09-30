<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeOwner extends Command
{
    protected $signature = 'users:make-owner {email} {--force : Skip the confirmation prompt}';

    protected $description = 'Make a user the owner. The previous owner becomes an admin.';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No user found with that email');

            return self::FAILURE;
        }

        if ($user->isOwner()) {
            $this->info("{$user->name} ({$user->email}) is already the owner.");

            return self::SUCCESS;
        }

        $currentOwner = User::where('role', User::ROLE_OWNER)->first();

        if ($currentOwner && ! $this->option('force')) {
            $confirmed = $this->confirm(
                "{$currentOwner->name} ({$currentOwner->email}) is the current owner and will become an admin. Continue?"
            );

            if (! $confirmed) {
                $this->info('Cancelled.');

                return self::FAILURE;
            }
        }

        $user->becomeOwner();

        $this->info("{$user->name} ({$user->email}) is now the owner.");

        return self::SUCCESS;
    }
}