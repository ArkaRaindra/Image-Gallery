<?php

namespace App\Filament\Resources\Tags\Tables;

use App\Filament\Resources\Tags\TagResource;
use App\Models\Tag;
use App\Support\TagColors;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->color(fn (Tag $record): array => TagColors::filament($record->category))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->badge()
                    ->color(fn (?string $state): array => TagColors::filament($state))
                    ->sortable(),
                TextColumn::make('post_count')
                    ->label('Posts')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'general' => 'General',
                        'artist' => 'Artist',
                        'character' => 'Character',
                        'copyright' => 'Copyright',
                        'meta' => 'Meta',
                    ]),
            ])
            ->defaultSort('post_count', 'desc')
            ->recordUrl(fn (Tag $record): string => TagResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
