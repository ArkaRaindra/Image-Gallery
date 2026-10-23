<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A snapshot of an artist taken after one edit.
 */
class ArtistVersion extends Model
{
    protected $fillable = [
        'artist_id',
        'updater_id',
        'version',
        'name',
        'other_names',
        'group_name',
        'urls',
        'is_deleted',
        'is_new',
        'change_summary',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'is_new' => 'boolean',
    ];

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updater_id');
    }

    /**
     * @return list<string>
     */
    public function otherNameList(): array
    {
        return preg_split('/\s+/', trim((string) $this->other_names), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    /**
     * URLs of this version as {url: is_active}.
     *
     * @return array<string, bool>
     */
    public function urlMap(): array
    {
        $map = [];

        foreach (preg_split('/\R/', (string) $this->urls) ?: [] as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $active = ! str_starts_with($line, '-');
            $url = $active ? $line : trim(substr($line, 1));

            if ($url !== '') {
                $map[$url] = $active;
            }
        }

        return $map;
    }

    /**
     * What the edit did, one label per change (New, Renamed, URLs, ...).
     *
     * @return list<string>
     */
    public function changeLabels(): array
    {
        return array_values(array_filter(explode(',', (string) $this->change_summary)));
    }
}
