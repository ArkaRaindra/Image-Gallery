<?php

namespace App\Filament\Resources\Roles\Tables;

use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        User::ROLE_OWNER => 'warning',
                        User::ROLE_ADMIN => 'danger',
                        User::ROLE_MODERATOR => 'success',
                        default => 'info',
                    }),
                TextColumn::make('permissions_count')
                    ->label('Permissions')
                    ->counts('permissions'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
