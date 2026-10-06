<?php

namespace App\Models;

use App\Models\Scopes\HidePendingDeletionScope;
use App\Support\PanelNotifications;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[ScopedBy(HidePendingDeletionScope::class)]
class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'uploader_id',
        'parent_id',
        'file_path',
        'file_name',
        'file_ext',
        'file_size',
        'width',
        'height',
        'thumbnail_path',
        'md5',
        'rating',
        'source',
        'description',
        'score',
        'is_approved',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public const VIDEO_EXTENSIONS = ['mp4', 'webm', 'mov', 'avi', 'mkv'];

    public function isVideo(): bool
    {
        return in_array(strtolower($this->file_ext), self::VIDEO_EXTENSIONS, true);
    }

    public function hasCustomThumbnail(): bool
    {
        return $this->thumbnail_path !== $this->file_path;
    }

    public function thumbnailIsVideo(): bool
    {
        return in_array(strtolower(pathinfo($this->thumbnail_path, PATHINFO_EXTENSION)), self::VIDEO_EXTENSIONS, true);
    }

    public function canManageThumbnail(?User $user): bool
    {
        return $this->isManagedBy($user);
    }

    public function canManageNotes(?User $user): bool
    {
        return $this->isManagedBy($user);
    }

    public function isManagedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->isAdmin() || $user->id === $this->uploader_id;
    }

    protected static function booted(): void
    {
        static::saving(function (self $post) {
            if (! $post->isDirty('is_approved')) {
                return;
            }

            if (! $post->is_approved) {
                $post->approved_by = null;
                $post->approved_at = null;

                return;
            }

            // A post only counts as approved by someone when another user
            // (an admin) lets it through. An admin's own upload is not an approval.
            $approverId = auth()->id();

            if ($approverId && $approverId !== $post->uploader_id) {
                $post->approved_by = $approverId;
                $post->approved_at = now();
            }
        });

        static::created(function (self $post) {
            if (! $post->is_approved) {
                PanelNotifications::postNeedsApproval($post);
            }
        });

        static::deleting(function (self $post) {
            DeletionReport::closeForComments(
                Comment::withoutGlobalScopes()->where('post_id', $post->id)->pluck('id'),
                'The post was deleted.',
            );
        });

        static::deleted(function (self $post) {
            DeletionReport::closeForSubject($post, 'The post was deleted directly.');
            DeletionReport::logDirectDeletion($post);
            Tag::recalculateAllPostCounts();
        });
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Children that are visible on the public site.
     */
    public function visibleChildren(): HasMany
    {
        return $this->children()->approved();
    }

    /**
     * Returns an error message when $parentId cannot become this post's
     * parent (itself, or one of its own descendants), otherwise null.
     */
    public function parentAssignmentError(int $parentId): ?string
    {
        if ($parentId === $this->getKey()) {
            return 'A post cannot be its own parent.';
        }

        $visited = [];
        $cursor = $parentId;

        while ($cursor && ! isset($visited[$cursor])) {
            if ($cursor === $this->getKey()) {
                return 'That post is a descendant of this post, so it cannot be its parent.';
            }

            $visited[$cursor] = true;

            $cursor = (int) (self::withoutGlobalScopes()->whereKey($cursor)->value('parent_id') ?? 0);
        }

        return null;
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tags')
            ->using(PostTag::class)
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function deletionReports(): MorphMany
    {
        return $this->morphMany(DeletionReport::class, 'reportable');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class)->orderBy('id');
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function isFavoritedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->favoritedBy()->where('users.id', $user->id)->exists();
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Adds `children_count` (approved, non-hidden children) to each post.
     */
    public function scopeWithVisibleChildrenCount($query)
    {
        return $query->withCount('visibleChildren as children_count');
    }

    public function tagStringByCategory(string $category): string
    {
        return $this->tags
            ->where('category', $category)
            ->pluck('name')
            ->implode(' ');
    }

    public function humanFileSize(): string
    {
        $bytes = $this->file_size;

        return match (true) {
            $bytes >= 1_048_576 => round($bytes / 1_048_576, 2).' MB',
            $bytes >= 1024 => round($bytes / 1024, 1).' KB',
            default => $bytes.' B',
        };
    }
}