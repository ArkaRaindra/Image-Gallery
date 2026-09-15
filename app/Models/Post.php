<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'uploader_id',
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
    ];

    protected $casts = [
        'is_approved' => 'boolean',
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
        if (! $user) {
            return false;
        }

        return $user->isAdmin() || $user->id === $this->uploader_id;
    }

    protected static function booted(): void
    {
        static::deleted(function (self $post) {
            Tag::recalculateAllPostCounts();
        });
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
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
            $bytes >= 1_048_576 => round($bytes / 1_048_576, 2) . ' MB',
            $bytes >= 1024 => round($bytes / 1024, 1) . ' KB',
            default => $bytes . ' B',
        };
    }
}