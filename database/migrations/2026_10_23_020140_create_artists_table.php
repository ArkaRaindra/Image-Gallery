<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * An artist is the extra information kept for a tag of the "artist"
     * category: other names, group name, URLs and a history of every edit.
     */
    public function up(): void
    {
        Schema::create('artists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('other_names')->nullable();
            $table->string('group_name')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->unsignedInteger('version')->default(0);
            $table->foreignId('updater_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('is_deleted');
            $table->index('updated_at');
        });

        Schema::create('artist_urls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->constrained()->cascadeOnDelete();
            $table->text('url');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('created_at');
            $table->index('updated_at');
        });

        Schema::create('artist_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('updater_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('version');
            $table->string('name');
            $table->string('other_names')->nullable();
            $table->string('group_name')->nullable();
            // One URL per line; a leading "-" marks a URL that is no longer active.
            $table->text('urls')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_new')->default(false);
            $table->string('change_summary')->nullable();
            $table->timestamps();

            $table->unique(['artist_id', 'version']);
            $table->index('created_at');
        });

        // Tags that are already in the artist category become artists, each
        // with a first version.
        DB::table('tags')->where('category', 'artist')->orderBy('id')->chunkById(200, function ($tags): void {
            foreach ($tags as $tag) {
                $at = $tag->created_at ?? now();

                $artistId = DB::table('artists')->insertGetId([
                    'tag_id' => $tag->id,
                    'other_names' => $tag->other_names,
                    'group_name' => null,
                    'is_deleted' => false,
                    'version' => 1,
                    'updater_id' => null,
                    'created_at' => $at,
                    'updated_at' => $at,
                ]);

                DB::table('artist_versions')->insert([
                    'artist_id' => $artistId,
                    'updater_id' => null,
                    'version' => 1,
                    'name' => $tag->name,
                    'other_names' => $tag->other_names,
                    'group_name' => null,
                    'urls' => null,
                    'is_deleted' => false,
                    'is_new' => true,
                    'change_summary' => 'New',
                    'created_at' => $at,
                    'updated_at' => $at,
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artist_versions');
        Schema::dropIfExists('artist_urls');
        Schema::dropIfExists('artists');
    }
};
