<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNotIn('role', ['owner', 'admin', 'moderator', 'member'])
            ->update(['role' => 'member']);

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('ownwer_slot')
                    ->nullable()
                    ->virtualAs("CASE WHEN role = 'owner' THEN 1 ELSE NULL END")
                    ->unique('users_single_owner_unique');
            });
            return;
        }
        DB::statement("CREATE UNIQUE INDEX users_single_owner_unique ON users (role) WHERE role = 'owner'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_single_owner_unique');
                $table->dropColumn('owner_slot');
            });
            return;
        }
        DB::statement('DROP INDEX users_single_owner_unique');
    }
};
