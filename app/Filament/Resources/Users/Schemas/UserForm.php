<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Username')
                    ->maxLength(50)
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->label('Password')
                    ->live()
                    ->password()
                    ->minLength(8)
                    ->required()
                    ->revealable(),
                Select::make('role')
                    ->label('Role'),
                FileUpload::make('avatar_path')
                    ->label('Profile Picture')
                    ->columnSpanFull()
                    ->disk('public')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                    ->directory('avatars')
                    ->image()
                    ->imagePreviewHeight('250px'),
            ]);
    }
}
