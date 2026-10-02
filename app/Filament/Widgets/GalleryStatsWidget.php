<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GalleryStatsWidget extends BaseWidget
{
    protected function getColumns(): int
    {
        return 6;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Total Posts', Post::withoutGlobalScopes()->count())
                ->description('All posts in the gallery')
                ->icon('heroicon-o-photo')
                ->color('success')
                ->columnSpan(3),

            Stat::make('Pending Posts', Post::withoutGlobalScopes()->where('is_approved', false)->count())
                ->description('Awaiting approval')
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->columnSpan(3),

            Stat::make('Total Tags', Tag::count())
                ->description('Unique tags in use')
                ->icon('heroicon-o-tag')
                ->color('info')
                ->columnSpan(3),

            Stat::make('Total Users', User::count())
                ->description('Total users in the system')
                ->icon('heroicon-o-user-group')
                ->color('danger')
                ->columnSpan(3),

            Stat::make('Admins', User::where('role', User::ROLE_ADMIN)->count())
                ->description('Total Admin')
                ->icon('heroicon-o-shield-check')
                ->color('danger')
                ->columnSpan(2),

            Stat::make('Moderators', User::where('role', User::ROLE_MODERATOR)->count())
                ->description('Total Moderator')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('success')
                ->columnSpan(2),

            Stat::make('Members', User::where('role', User::ROLE_MEMBER)->count())
                ->description('Total Member')
                ->icon('heroicon-o-user')
                ->color('info')
                ->columnSpan(2),
        ];
    }
}
