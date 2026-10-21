<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The wiki of a tag is its `description`. These columns add the alias
     * names and the bookkeeping needed for "recent changes".
     */
    public function up(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->string('other_names')->nullable()->after('description');
            $table->unsignedInteger('wiki_version')->default(0)->after('other_names');
            $table->timestamp('wiki_updated_at')->nullable()->after('wiki_version');
            $table->foreignId('wiki_updater_id')->nullable()->after('wiki_updated_at')
                ->constrained('users')->nullOnDelete();

            $table->index('wiki_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropConstrainedForeignId('wiki_updater_id');
            $table->dropIndex(['wiki_updated_at']);
            $table->dropColumn(['other_names', 'wiki_version', 'wiki_updated_at']);
        });
    }
};