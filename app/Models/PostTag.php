<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PostTag extends Pivot
{
    protected $table = 'post_tags';

    protected static function booted(): void
    {
        static::created(function (self $pivot) {
            Tag::whereKey($pivot->tag_id)->increment('post_count');
        });

        static::deleted(function (self $pivot) {
            Tag::whereKey($pivot->tag_id)->decrement('post_count');
        });
    }
}