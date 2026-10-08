<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Filament\Resources\Posts\Actions\ViewPostAction;
use App\Models\DeletionReport;
use App\Models\Post;
use App\Models\Tag;
use App\Support\TagColors;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withExists([
                'deletionReports as has_pending_deletion' => fn (Builder $reports) => $reports
                    ->where('status', DeletionReport::STATUS_PENDING),
            ]))
            ->columns([
                ImageColumn::make('thumbnail_path')
                    ->label('Preview')
                    ->disk('public')
                    ->square(),
                TextColumn::make('rating')
                    ->badge(),
                TextColumn::make('uploader.name')
                    ->label('Uploader')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('score')
                    ->sortable(),
                IconColumn::make('is_approved')
                    ->boolean()
                    ->label('Approved'),
                IconColumn::make('has_pending_deletion')
                    ->label('Deletion requested')
                    ->boolean()
                    ->trueIcon(Heroicon::ExclamationTriangle)
                    ->trueColor('danger')
                    ->falseIcon(Heroicon::Minus)
                    ->falseColor('gray'),
                TextColumn::make('tags.name')
                    ->badge()
                    ->color(fn (string $state, Post $record): array => TagColors::filament(
                        $record->tags->firstWhere('name', $state)?->category,
                    ))
                    ->limit(5),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_approved'),
                SelectFilter::make('uploader')
                    ->relationship('uploader', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('rating')
                    ->options([
                        'general' => 'General',
                        'sensitive' => 'Sensitive',
                        'questionable' => 'Questionable',
                        'explicit' => 'Explicit',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewPostAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('approve')
                        ->label('Approve')
                        ->icon(Heroicon::Check)
                        ->action(function ($records) {
                            $records->each->update(['is_approved' => true]);
                            Tag::recalculateAllPostCounts();
                        }),
                ]),
            ]);
    }
}
