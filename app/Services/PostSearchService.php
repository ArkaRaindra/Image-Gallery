<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PostSearchService
{
    public function search(?string $query): Builder
    {
        $builder = Post::query()->approved();

        $tokens = collect(explode(' ', trim((string) $query)))
            ->map(fn ($t) => trim($t))
            ->filter();

        foreach ($tokens as $token) {
            match (true) {
                Str::startsWith($token, '-') => $this->applyExclude($builder, Str::after($token, '-')),
                Str::startsWith($token, 'rating:') => $this->applyRating($builder, Str::after($token, 'rating:')),
                Str::startsWith($token, 'user:') => $this->applyUploader($builder, Str::after($token, 'user:')),
                Str::startsWith($token, 'fav:') => $this->applyFavoritedBy($builder, Str::after($token, 'fav:')),
                Str::startsWith($token, 'parent:') => $this->applyParent($builder, Str::after($token, 'parent:')),
                Str::startsWith($token, 'child:') => $this->applyChild($builder, Str::after($token, 'child:')),
                default => $this->applyInclude($builder, $token),
            };
        }

        return $builder->latest('id');
    }

    protected function applyInclude(Builder $builder, string $tag): void
    {
        $builder->whereHas('tags', function (Builder $q) use ($tag) {
            $this->matchTagName($q, $tag);
        });
    }

    protected function applyExclude(Builder $builder, string $tag): void
    {
        $builder->whereDoesntHave('tags', function (Builder $q) use ($tag) {
            $this->matchTagName($q, $tag);
        });
    }

    protected function applyRating(Builder $builder, string $rating): void
    {
        if (in_array($rating, ['general', 'sensitive', 'questionable', 'explicit'], true)) {
            $builder->where('rating', $rating);
        }
    }

    /**
     * user:name - posts uploaded by the user. Underscores stand for spaces.
     */
    protected function applyUploader(Builder $builder, string $name): void
    {
        $builder->whereHas('uploader', function (Builder $q) use ($name) {
            $q->whereRaw("REPLACE(users.name, ' ', '_') = ?", [$name]);
        });
    }

    /**
     * fav:name - posts the user has favorited. Underscores stand for spaces.
     */
    protected function applyFavoritedBy(Builder $builder, string $name): void
    {
        $builder->whereHas('favoritedBy', function (Builder $q) use ($name) {
            $q->whereRaw("REPLACE(users.name, ' ', '_') = ?", [$name]);
        });
    }

    /**
     * parent:none - posts without a parent.
     * parent:any  - posts that have a parent.
     * parent:123  - post 123 together with its children.
     */
    protected function applyParent(Builder $builder, string $value): void
    {
        match (true) {
            $value === 'none' => $builder->whereNull('posts.parent_id'),
            $value === 'any' => $builder->whereNotNull('posts.parent_id'),
            ctype_digit($value) => $builder->where(function (Builder $q) use ($value): void {
                $q->where('posts.id', (int) $value)->orWhere('posts.parent_id', (int) $value);
            }),
            default => null,
        };
    }

    /**
     * child:none - posts without children.
     * child:any  - posts that have children.
     */
    protected function applyChild(Builder $builder, string $value): void
    {
        match ($value) {
            'none' => $builder->whereDoesntHave('visibleChildren'),
            'any' => $builder->whereHas('visibleChildren'),
            default => null,
        };
    }

    protected function matchTagName(Builder $q, string $tag): void
    {
        if (Str::contains($tag, '*')) {
            $q->where('name', 'like', str_replace('*', '%', $tag));
        } else {
            $q->where('name', $tag);
        }
    }
}