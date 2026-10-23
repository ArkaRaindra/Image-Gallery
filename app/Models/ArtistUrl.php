<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistUrl extends Model
{
    protected $fillable = [
        'artist_id',
        'url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }
}
