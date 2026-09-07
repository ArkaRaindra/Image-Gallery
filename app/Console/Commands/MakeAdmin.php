<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    protected $signature = 'users:make-admin {email}';

    protected $description = 'Promote a user to the admin role';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No user found with that email.');

            return self::FAILURE;
        }

        $user->role = 'admin';
        $user->save();

        $this->info("{$user->name} ({$user->email}) is now an admin.");

        return self::SUCCESS;
    }
}