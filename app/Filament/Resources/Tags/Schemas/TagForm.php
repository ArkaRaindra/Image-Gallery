<?php

namespace App\Filament\Resources\Tags\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Select::make('category')
                    ->options([
                        'general' => 'General',
                        'artist' => 'Artist',
                        'character' => 'Character',
                        'copyright' => 'Copyright',
                        'meta' => 'Meta',
                    ])
                    ->required()
                    ->default('general'),

                Textarea::make('description')
                    ->columnspanFull(),
            ]);
    }
}
