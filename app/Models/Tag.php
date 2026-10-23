<?php

namespace App\Models;

use App\Services\ArtistService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    /**
     * Every tag category, in the order they are offered in forms.
     *
     * @var list<string>
     */
    public const CATEGORIES = ['general', 'artist', 'character', 'copyright', 'meta'];

    protected $fillable = [
        'name',
        'category',
        'description',
        'other_names',
    ];

    protected $casts = [
        'wiki_updated_at' => 'datetime',
    ];

    /**
     * False while an artist is being made together with its tag, so the artist
     * is not made twice.
     */
    private static bool $syncArtistRecord = true;

    protected static function booted(): void
    {
        static::saving(function (self $tag) {
            $tag->name = self::normalizeName($tag->name);

            // An editor that was left empty (<p></p>) means "no wiki".
            if ($tag->isDirty('description') && ! self::htmlHasContent($tag->description)) {
                $tag->description = null;
            }
        });

        // Every change of the wiki text or the other names becomes a version,
        // whether it was made on the wiki pages or in the admin panel.
        static::saved(function (self $tag): void {
            // First: the wiki bookkeeping saves the tag again and would reset
            // what wasChanged() reports.
            $tag->syncArtistRecord();
            $tag->recordWikiVersion();
        });
    }

    /**
     * Runs the callback without making an artist for a tag that enters the
     * artist category.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function withoutArtistSync(callable $callback): mixed
    {
        $previous = self::$syncArtistRecord;
        self::$syncArtistRecord = false;

        try {
            return $callback();
        } finally {
            self::$syncArtistRecord = $previous;
        }
    }

    public function artist(): HasOne
    {
        return $this->hasOne(Artist::class);
    }

    /**
     * True when the HTML shows something: text or an image.
     */
    public static function htmlHasContent(?string $html): bool
    {
        $html = (string) $html;

        if (stripos($html, '<img') !== false) {
            return true;
        }

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5);

        return (string) preg_replace('/[\s\x{00A0}]+/u', '', $text) !== '';
    }

    /**
     * A tag has a wiki page when its description has content.
     */
    public function hasWiki(): bool
    {
        return self::htmlHasContent($this->description);
    }

    /**
     * Tags that have a wiki page.
     *
     * @param  Builder<Tag>  $query
     * @return Builder<Tag>
     */
    public function scopeWithWiki(Builder $query): Builder
    {
        return $query->whereNotNull('description')->where('description', '!=', '');
    }

    public function wikiVersions(): HasMany
    {
        return $this->hasMany(WikiPageVersion::class)->orderByDesc('version');
    }

    public function wikiUpdater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wiki_updater_id');
    }

    /**
     * The tag name as it is shown (spaces instead of underscores).
     */
    public function displayName(): string
    {
        return str_replace('_', ' ', $this->name);
    }

    /**
     * Alias names shown under the title of the wiki page.
     *
     * @return list<string>
     */
    public function otherNameList(): array
    {
        return preg_split('/\s+/', trim((string) $this->other_names), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    /**
     * Text color of the tag name on the light theme, by category.
     */
    public function categoryTextClass(): string
    {
        return match ($this->category) {
            'artist' => 'text-red-700',
            'character' => 'text-green-800',
            'copyright' => 'text-purple-700',
            'meta' => 'text-amber-700',
            default => 'text-sky-700',
        };
    }

    /**
     * A tag that is new in the artist category, or was moved into it, gets an
     * artist record.
     */
    private function syncArtistRecord(): void
    {
        if (! self::$syncArtistRecord || $this->category !== 'artist') {
            return;
        }

        if (! $this->wasRecentlyCreated && ! $this->wasChanged('category')) {
            return;
        }

        app(ArtistService::class)->ensureForTag($this);
    }

    private function recordWikiVersion(): void
    {
        $bodyChanged = $this->wasChanged('description');
        $namesChanged = $this->wasChanged('other_names');

        if (! $bodyChanged && ! $namesChanged) {
            return;
        }

        $currentVersion = (int) $this->wiki_version;

        // A tag that never had a wiki and still has none has nothing to record.
        if ($currentVersion === 0 && ! $this->hasWiki()) {
            return;
        }

        $isNew = $currentVersion === 0;
        $version = $currentVersion + 1;
        $updaterId = auth()->check() ? (int) auth()->id() : null;

        $summary = $isNew
            ? 'New'
            : implode(', ', array_filter([
                $bodyChanged ? 'Body' : null,
                $namesChanged ? 'Other names' : null,
            ]));

        $this->wikiVersions()->create([
            'updater_id' => $updaterId,
            'other_names' => $this->other_names,
            'body' => (string) $this->description,
            'version' => $version,
            'is_new' => $isNew,
            'change_summary' => $summary,
        ]);

        $this->forceFill([
            'wiki_version' => $version,
            'wiki_updated_at' => now(),
            'wiki_updater_id' => $updaterId,
        ])->saveQuietly();
    }

    /**
     * The form a tag name is always stored in: lowercase, underscores for spaces.
     */
    public static function normalizeName(string $name): string
    {
        return Str::of($name)
            ->lower()
            ->replace(' ', '_')
            ->trim('_')
            ->toString();
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_tags')
            ->using(PostTag::class)
            ->withTimestamps();
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Count only approved posts that are not hidden by a pending deletion request.
     */
    public static function recalculateAllPostCounts(): void
    {
        DB::statement('
            UPDATE tags
            SET post_count = (
                SELECT COUNT(*)
                FROM post_tags pt
                INNER JOIN posts p ON p.id = pt.post_id
                WHERE pt.tag_id = tags.id
                    AND p.is_approved = 1
                    AND NOT EXISTS (
                        SELECT 1
                        FROM deletion_reports dr
                        WHERE dr.reportable_id = p.id
                            AND dr.reportable_type = ?
                            AND dr.status = ?
                    )
            )
        ', [(new Post)->getMorphClass(), DeletionReport::STATUS_PENDING]);
    }
}
