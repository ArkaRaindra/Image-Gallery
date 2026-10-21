<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The Wiki menu opens help:home and the Help link opens help:wiki, so both
     * exist as (meta) tags that already have wiki content.
     */
    public function up(): void
    {
        $now = now();

        foreach (self::pages() as $name => $body) {
            if (DB::table('tags')->where('name', $name)->exists()) {
                continue;
            }

            $tagId = DB::table('tags')->insertGetId([
                'name' => $name,
                'category' => 'meta',
                'post_count' => 0,
                'description' => $body,
                'other_names' => null,
                'wiki_version' => 1,
                'wiki_updated_at' => $now,
                'wiki_updater_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('wiki_page_versions')->insert([
                'tag_id' => $tagId,
                'updater_id' => null,
                'other_names' => null,
                'body' => $body,
                'version' => 1,
                'is_new' => true,
                'change_summary' => 'New',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('tags')->whereIn('name', array_keys(self::pages()))->delete();
    }

    /**
     * @return array<string, string>
     */
    private static function pages(): array
    {
        return [
            'help:home' => <<<'HTML'
<h2>Welcome to the Wiki Help!</h2>
<h4>About</h4>
<p>This wiki explains the tags used on this site. Every wiki page belongs to a tag: the page of a tag is the same text you see in the Wiki tab on the posts page.</p>
<h4>Guide</h4>
<ul>
<li>Searching: use the search box on the posts page with tags such as <code>rating:general</code>, <code>parent:123</code> or <code>child:any</code>.</li>
<li>Wiki: see <a href="/wiki_pages/help:wiki">help:wiki</a> to learn how wiki pages work.</li>
<li>Notes: add translations and comments on image posts through the Notes menu.</li>
</ul>
<h4>Contributing</h4>
<p>Log in to write the wiki of a tag that does not have one yet, or to edit an existing wiki page. Every edit is kept in the page history.</p>
HTML,
            'help:wiki' => <<<'HTML'
<h2>About the Wiki</h2>
<p>Wiki pages explain the meaning of tags. <a href="/wiki_pages/help:home">help:home</a> lists the help topics.</p>
<h4>Menu</h4>
<ul>
<li>Search: find wiki pages by title, other names or text. The quick search box in the sub menu looks at titles only.</li>
<li>New: write the wiki of an existing tag that does not have one yet. Pick the tag from the dropdown. A tag that already has a wiki page cannot get a new one, edit it instead.</li>
<li>Changes: every edit of every wiki page, newest first.</li>
<li>Help: this page.</li>
</ul>
<h4>On a wiki page</h4>
<ul>
<li>Other names are shown under the title as aliases.</li>
<li>Posts: the number of posts with the tag, and a link to them.</li>
<li>History: all versions of the page.</li>
<li>Edit: any logged-in user can edit a page.</li>
<li>diff: compares a version with the version before it. Removed text is marked red and added text is marked green. A new page has no diff because there is nothing to compare it with.</li>
</ul>
<h4>Writing</h4>
<p>The editor supports headings, bold, italic, underline, lists, links and images. The HTML button switches to the HTML source, where other HTML tags can be used. Unsafe HTML such as scripts is removed when the page is saved.</p>
HTML,
        ];
    }
};