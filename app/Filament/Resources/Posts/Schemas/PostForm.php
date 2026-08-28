<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('file_path')
                    ->label('File')
                    ->image()
                    ->disk('public')
                    ->visibility('public')
                    ->directory('posts')
                    ->required(),

                Select::make('rating')
                    ->options([
                        'general' => 'General',
                        'sensitive' => 'Sensitive',
                        'questionable' => 'Questionable',
                        'explicit' => 'Explicit',
                    ])
                    ->required()
                    ->default('general'),

                Select::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                        Select::make('category')
                            ->options([
                                'general' => 'General',
                                'artist' => 'Artist',
                                'character' => 'Character',
                                'copyright' => 'Copyright',
                                'meta' => 'Meta',
                            ])
                            ->default('general')
                            ->required(),
                    ]),

                TextInput::make('source')
                    ->url()
                    ->maxLength(255),

                Textarea::make('description')
                    ->columnSpanFull(),

                Checkbox::make('is_approved')
                    ->label('Approved')
                    ->default(false),
            ]);
    }
}