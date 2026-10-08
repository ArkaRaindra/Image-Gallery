<?php

namespace App\Filament\Resources\Tags\Schemas;

use App\Models\Tag;
use App\Support\TagColors;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class TagInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tag')
                    ->columns(3)
                    ->columnSpanFull()
                    ->components([
                        TextEntry::make('name')
                            ->color(fn (Tag $record): array => TagColors::filament($record->category))
                            ->weight(FontWeight::Bold),

                        TextEntry::make('category')
                            ->badge()
                            ->color(fn (?string $state): array => TagColors::filament($state))
                            ->formatStateUsing(fn (?string $state): string => ucfirst((string) $state)),

                        TextEntry::make('post_count')
                            ->label('Approved posts'),

                        TextEntry::make('created_at')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->dateTime(),
                    ]),

                Section::make('Wiki content')
                    ->columnSpanFull()
                    ->components([
                        ViewEntry::make('description')
                            ->hiddenLabel()
                            ->view('filament.tags.wiki-content'),
                    ]),
            ]);
    }
}
