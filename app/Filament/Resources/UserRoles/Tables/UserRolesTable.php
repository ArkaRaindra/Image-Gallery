<?php

namespace App\Filament\Resources\UserRoles\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserRolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultsort('role', 'desc')
            ->columns([
                ImageColumn::make('avatar_path')
                    ->label('Profile Picture')
                    ->disk('public'),
                TextColumn::make('name')
                    ->label('Username')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email'),
                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        User::ROLE_OWNER => 'warning',
                        User::ROLE_ADMIN => 'danger',
                        User::ROLE_MODERATOR => 'success',
                        default => 'gray',
                    })
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query
                        ->orderByRole($direction)
                        ->orderBy('name'))
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Role')
                    ->options(User::roleOptions()),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Change role'),
            ]);
    }
}
