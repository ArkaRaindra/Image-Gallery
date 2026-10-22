<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * The accounts come from AccountSeeder (one per role) and the roles and
     * permissions from RolesAndPermissionsSeeder, which runs last because
     * model events are off here: it is what links the users to their Spatie
     * role.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call(AccountSeeder::class);

        $this->call(RolesAndPermissionsSeeder::class);
    }
}
