<?php

namespace App\Filament\Resources\Tags\RelationManagers;

use App\Filament\Resources\Posts\Actions\ViewPostAction;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use App\Models\Scopes\HidePendingDeletionScope;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

/**
 * Every post that uses the tag, shown on the tag's page.
 *
 * Unlike the "Approved posts" counter on the tag, this list also contains
 * unapproved posts and posts hidden by a pending deletion request.
 */
class PostsRelationManager extends RelationManager
{
    protected static string $relationship = 'posts';

    protected static ?string $title = 'Posts using this tag';

    #[Override]
    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->withoutGlobalScope(HidePendingDeletionScope::class))
            ->columns([
                ImageColumn::make('thumbnail_path')
                    ->label('Preview')
                    ->disk('public')
                    ->square(),
                // The post_tags pivot also has id and created_at, so sort on the posts table explicitly.
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('posts.id', $direction)),
                TextColumn::make('rating')
                    ->badge(),
                TextColumn::make('uploader.name')
                    ->label('Uploader')
                    ->placeholder('-'),
                TextColumn::make('score')
                    ->sortable(),
                IconColumn::make('is_approved')
                    ->boolean()
                    ->label('Approved'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('posts.created_at', $direction)),
            ])
            ->filters([
                TernaryFilter::make('is_approved')
                    ->label('Approved'),
                SelectFilter::make('rating')
                    ->options([
                        'general' => 'General',
                        'sensitive' => 'Sensitive',
                        'questionable' => 'Questionable',
                        'explicit' => 'Explicit',
                    ]),
            ])
            ->defaultSort(fn (Builder $query): Builder => $query->orderByDesc('posts.id'))
            ->recordActions([
                ViewPostAction::make(),
                Action::make('edit')
                    ->label('Edit')
                    ->icon(Heroicon::PencilSquare)
                    ->url(fn (Post $record): string => PostResource::getUrl('edit', ['record' => $record])),
            ])
            ->emptyStateHeading('No posts use this tag yet');
    }
}
