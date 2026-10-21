<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WikiPageVersion extends Model
{
    protected $fillable = [
        'tag_id',
        'updater_id',
        'other_names',
        'body',
        'version',
        'is_new',
        'change_summary',
    ];

    protected $casts = [
        'is_new' => 'boolean',
    ];

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updater_id');
    }

    /**
     * The version this one replaced, or null for the first version of a page.
     */
    public function previous(): ?self
    {
        return static::query()
            ->where('tag_id', $this->tag_id)
            ->where('version', '<', $this->version)
            ->orderByDesc('version')
            ->first();
    }
}