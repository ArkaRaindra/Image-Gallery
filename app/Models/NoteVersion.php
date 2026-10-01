<?php

namespace App\Models;

use App\Models\Scopes\RequiresVisiblePostScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy(RequiresVisiblePostScope::class)]
class NoteVersion extends Model
{
    protected $fillable = [
        'note_id',
        'post_id',
        'updater_id',
        'body',
        'x',
        'y',
        'width',
        'height',
        'version',
        'is_new',
    ];

    protected $casts = [
        'x' => 'float',
        'y' => 'float',
        'width' => 'float',
        'height' => 'float',
        'is_new' => 'boolean',
    ];

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updater_id');
    }
}
