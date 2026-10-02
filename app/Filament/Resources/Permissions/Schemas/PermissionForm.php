<?php

namespace App\Filament\Resources\Permissions\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Permission')
                    ->helperText('Lowercase with underscores, e.g. publish_post.')
                    ->maxLength(255)
                    ->regex('/^[a-z][a-z0-9_]*$/')
                    ->unique(ignoreRecord: true)
                    ->required(),
                Hidden::make('guard_name')
                    ->default('web'),
            ]);
    }
}
