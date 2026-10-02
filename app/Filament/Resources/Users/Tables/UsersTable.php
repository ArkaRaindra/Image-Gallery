<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('role', 'desc')
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
                        ->orderBy('name')),
                TextColumn::make('created_at')
                    ->label('Joined at')
                    ->date('d-m-Y')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('role')
                    ->form([
                        CheckboxList::make('roles')
                            ->label('Role')
                            ->options(User::roleOptions()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['roles'] ?? null,
                            fn (Builder $query, $roles) => $query->whereHas('roles', fn ($query) => $query->whereIn('name', $roles)
                            )
                        );
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords('delete'),
                ]),
            ]);
    }
}
