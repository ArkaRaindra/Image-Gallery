<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Note extends Model
{
    protected $fillable = [
        'post_id',
        'creator_id',
        'body',
        'x',
        'y',
        'width',
        'height',
        'version',
    ];

    protected $casts = [
        'x' => 'float',
        'y' => 'float',
        'width' => 'float',
        'height' => 'float',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(NoteVersion::class)->orderByDesc('version');
    }

    public function recordVersion(?int $updaterId, bool $isNew = false): NoteVersion
    {
        return $this->versions()->create([
            'post_id' => $this->post_id,
            'updater_id' => $updaterId,
            'body' => $this->body,
            'x' => $this->x,
            'y' => $this->y,
            'width' => $this->width,
            'height' => $this->height,
            'version' => $this->version,
            'is_new' => $isNew,
        ]);
    }

    public function toOverlayArray(): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'x' => $this->x,
            'y' => $this->y,
            'width' => $this->width,
            'height' => $this->height,
            'version' => $this->version,
        ];
    }
}