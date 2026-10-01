<?php

namespace App\Filament\Resources\DeletionReports;

use App\Filament\Resources\DeletionReports\Pages\ListDeletionReports;
use App\Filament\Resources\DeletionReports\Tables\DeletionReportsTable;
use App\Models\DeletionReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

class DeletionReportResource extends Resource
{
    protected static ?string $model = DeletionReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Flag;

    protected static ?string $navigationLabel = 'Deletion Requests';

    protected static ?string $modelLabel = 'deletion request';

    protected static ?string $pluralModelLabel = 'deletion requests';

    public static function table(Table $table): Table
    {
        return DeletionReportsTable::configure($table);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    #[Override]
    public static function getNavigationBadge(): ?string
    {
        $pending = DeletionReport::query()->pending()->count();

        return $pending > 0 ? (string) $pending : null;
    }

    #[Override]
    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeletionReports::route('/'),
        ];
    }
}
