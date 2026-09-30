<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

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
                    ->label('Role')
                    ->options(function (?User $record): array {
                        $roles = self::actor()->assignableRoles();

                        if ($record !== null) {
                            $roles[] = $record->role;
                        }

                        return User::roleOptions($roles);
                    })
                    ->default(User::ROLE_MEMBER)
                    ->required(fn (?User $record): bool => ! self::isRoleLocked($record))
                    ->disabled(fn (?User $record): bool => self::isRoleLocked($record)),
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

    /**
     * The role of a user cannot be changed by themselves or by someone who
     * does not outrank them (this also locks the owner's role).
     */
    protected static function isRoleLocked(?User $record): bool
    {
        return $record !== null && ! self::actor()->outranks($record);
    }

    protected static function actor(): User
    {
        /** @var User $actor */
        $actor = auth()->user();

        return $actor;
    }
}