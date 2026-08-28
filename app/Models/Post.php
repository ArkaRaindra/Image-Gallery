<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tag')
            ->using(PostTag::class)
            ->withTimestamps();
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
}
