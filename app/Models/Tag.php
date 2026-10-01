<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
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
        return $this->belongsToMany(Post::class, 'post_tags')
            ->using(PostTag::class)
            ->withTimestamps();
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Count only approved posts that are not hidden by a pending deletion request.
     */
    public static function recalculateAllPostCounts(): void
    {
        DB::statement('
            UPDATE tags
            SET post_count = (
                SELECT COUNT(*)
                FROM post_tags pt
                INNER JOIN posts p ON p.id = pt.post_id
                WHERE pt.tag_id = tags.id
                    AND p.is_approved = 1
                    AND NOT EXISTS (
                        SELECT 1
                        FROM deletion_reports dr
                        WHERE dr.reportable_id = p.id
                            AND dr.reportable_type = ?
                            AND dr.status = ?
                    )
            )
        ', [(new Post)->getMorphClass(), DeletionReport::STATUS_PENDING]);
    }
}
