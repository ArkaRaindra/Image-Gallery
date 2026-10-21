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
        Schema::create('wiki_page_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->foreignId('updater_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('other_names')->nullable();
            $table->longText('body');
            $table->unsignedInteger('version');
            $table->boolean('is_new')->default(false);
            $table->string('change_summary')->nullable();
            $table->timestamps();

            $table->index(['tag_id', 'version']);
            $table->index('created_at');
        });

        // Tags that already have wiki content get it as their first version.
        DB::table('tags')->whereNotNull('description')->orderBy('id')->chunkById(200, function ($tags): void {
            foreach ($tags as $tag) {
                if (! self::hasContent($tag->description)) {
                    continue;
                }

                $at = $tag->updated_at ?? now();

                DB::table('wiki_page_versions')->insert([
                    'tag_id' => $tag->id,
                    'updater_id' => null,
                    'other_names' => $tag->other_names,
                    'body' => $tag->description,
                    'version' => 1,
                    'is_new' => true,
                    'change_summary' => 'New',
                    'created_at' => $at,
                    'updated_at' => $at,
                ]);

                DB::table('tags')->where('id', $tag->id)->update([
                    'wiki_version' => 1,
                    'wiki_updated_at' => $at,
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wiki_page_versions');
    }

    private static function hasContent(?string $html): bool
    {
        $html = (string) $html;

        if (stripos($html, '<img') !== false) {
            return true;
        }

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5);

        return (string) preg_replace('/[\s\x{00A0}]+/u', '', $text) !== '';
    }
};