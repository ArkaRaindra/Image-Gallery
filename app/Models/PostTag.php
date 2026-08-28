<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostTag extends Model
{
    protected $table = 'post_tag';

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
