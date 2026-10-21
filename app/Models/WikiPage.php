<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WikiPage extends Model
{
    protected $fillable = [
        'title',
        'other_names',
        'body',
        'creator_id',
        'updater_id',
        'version',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $page): void {
            $page->title = self::normalizeTitle((string) $page->title);
        });
    }

    /**
     * The form a wiki title is always stored in. Same rule as tag names:
     * lowercase, underscores for spaces.
     */
    public static function normalizeTitle(string $title): string
    {
        return Tag::normalizeName($title);
    }

    public static function findByTitle(string $title): ?self
    {
        return static::query()->where('title', self::normalizeTitle($title))->first();
    }

    /**
     * Newest edit first.
     *
     * @param  Builder<WikiPage>  $query
     * @return Builder<WikiPage>
     */
    public function scopeRecentlyUpdated(Builder $query): Builder
    {
        return $query->orderbyDesc('updated_at')->orderbyDesc('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updater_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(WikiPageVersion::class)->orderByDesc('version');
    }

    public function recordVersion(?int $updaterId, bool $isNew, string $changeSummary): WikiPageVersion
    {
        return $this->versions()->create([
            'updater_id' => $updaterId,
            'title' => $this->title,
            'other_names' => $this->other_names,
            'body' => $this->body,
            'version' => $this->version,
            'is_new' => $isNew,
            'change_summary' => $changeSummary,
        ]);
    }

     /**
     * The title as it is shown in headings and menus (spaces instead of underscores).
     */
    public function displayTitle(): string
    {
        return str_replace('_', ' ', $this->title); 
    }

    /**
     * @return list<string>
     */
    public function otherNameList(): array
    {
        return preg_split('/\s+/', trim((string) $this->other_names), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    /**
     * Number of posts that carry the tag of the same name (0 when there is no such tag).
     */
    public function postCount(): int
    {
        return (int) Tag::query()->where('name', $this->title)->value('post_count');
    }
}
