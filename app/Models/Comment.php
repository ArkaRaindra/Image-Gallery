<?php

namespace App\Models;

use App\Models\Scopes\HidePendingDeletionScope;
use App\Models\Scopes\RequiresVisiblePostScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[ScopedBy([HidePendingDeletionScope::class, RequiresVisiblePostScope::class])]
class Comment extends Model
{
    protected $fillable = [
        'post_id',
        'parent_id',
        'user_id',
        'author_name',
        'body',
        'score',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $comment) {
            DeletionReport::closeForComments(
                static::withoutGlobalScopes()->where('parent_id', $comment->id)->pluck('id'),
                'The comment it replies to was deleted.',
            );
        });

        static::deleted(function (self $comment) {
            DeletionReport::closeForSubject($comment, 'The comment was deleted directly.');
        });
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function deletionReports(): MorphMany
    {
        return $this->morphMany(DeletionReport::class, 'reportable');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->latest();
    }
}
