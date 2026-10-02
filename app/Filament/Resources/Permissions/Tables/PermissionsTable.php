<?php

namespace App\Filament\Resources\Permissions\Tables;

use App\Support\Permissions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;

class PermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Permission')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->state(fn (Permission $record): string => Permissions::isSystem($record->name) ? 'System' : 'Custom')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'System' ? 'warning' : 'gray'),
                TextColumn::make('roles_count')
                    ->label('Roles')
                    ->counts('roles'),
                TextColumn::make('created_at')
                    ->label('Created at')
                    ->date('d-m-Y')
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords('delete'),
                ]),
            ]);
    }
}
