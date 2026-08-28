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

    protected function matchTagName(Builder $q, string $tag): void
    {
        if (Str::contains($tag, '*')) {
            $q->where('name', 'like', str_replace('*', '%', $tag));
        } else {
            $q->where('name', $tag);
        }
    }
}