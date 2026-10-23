<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The extra information of a tag in the "artist" category. The name of an
 * artist is the name of its tag.
 */
class Artist extends Model
{
    protected $fillable = [
        'tag_id',
        'other_names',
        'group_name',
        'is_deleted',
        'version',
        'updater_id',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    public function urls(): HasMany
    {
        return $this->hasMany(ArtistUrl::class)->orderBy('id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ArtistVersion::class)->orderByDesc('version');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updater_id');
    }

    /**
     * Other names as a list.
     *
     * @return list<string>
     */
    public function otherNameList(): array
    {
        return preg_split('/\s+/', trim((string) $this->other_names), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    /**
     * The URLs as the text of the form: one per line, "-" in front of a URL
     * that is no longer active.
     */
    public function urlsText(): string
    {
        return $this->urls
            ->map(fn (ArtistUrl $url) => ($url->is_active ? '' : '-').$url->url)
            ->implode("\n");
    }
}
