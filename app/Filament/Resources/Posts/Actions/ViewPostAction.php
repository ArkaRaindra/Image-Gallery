<?php

namespace App\Filament\Resources\Posts\Actions;

use App\Models\Post;
use App\Models\Scopes\HidePendingDeletionScope;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;

/**
 * "View" button that shows a post's details in a modal, so the admin never
 * leaves the current page. Used by the posts table and by the posts list on
 * a tag's page.
 */
class ViewPostAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'viewPost';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('View')
            ->icon(Heroicon::Eye)
            ->color('gray')
            ->modalHeading(fn (Post $record): string => 'Post #'.$record->getKey())
            ->modalWidth('5xl')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->modalContent(fn (Post $record): View => view('filament.posts.details', static::detailsFor($record)));
    }

    /**
     * Data for the modal. Admins see every post in the family, including the
     * ones that are unapproved or hidden by a pending deletion request.
     *
     * @return array<string, mixed>
     */
    public static function detailsFor(Post $post): array
    {
        $post->loadMissing(['tags', 'uploader', 'approver']);

        $everything = fn () => Post::query()->withoutGlobalScope(HidePendingDeletionScope::class);

        $parent = $post->parent_id
            ? $everything()->find($post->parent_id)
            : null;

        /** @var Collection<int, Post> $siblings */
        $siblings = $post->parent_id
            ? $everything()
                ->where('parent_id', $post->parent_id)
                ->whereKeyNot($post->getKey())
                ->orderBy('id')
                ->get()
            : new Collection;

        /** @var Collection<int, Post> $children */
        $children = $everything()
            ->where('parent_id', $post->getKey())
            ->orderBy('id')
            ->get();

        return [
            'post' => $post,
            'parent' => $parent,
            'siblings' => $siblings,
            'children' => $children,
            'hasPendingDeletion' => (bool) ($post->has_pending_deletion
                ?? $post->deletionReports()->pending()->exists()),
        ];
    }
}
