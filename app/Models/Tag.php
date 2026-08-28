<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'description',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $tag) {
            $tag->name = Str::of($tag->name)
                ->lower()
                ->replace(' ', '_')
                ->trim('_')
                ->toString();
        });
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_tag')
            ->using(PostTag::class)
            ->withTimestamps();
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
