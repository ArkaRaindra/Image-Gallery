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
        return 2;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Total Posts', Post::count())
                ->description('All posts in the gallery')
                ->icon('heroicon-o-photo')
                ->color('success'),

            Stat::make('Pending Posts', Post::where('is_approved', false)->count())
                ->description('Awaiting approval')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Total Tags', Tag::count())
                ->description('Unique tags in use')
                ->icon('heroicon-o-tag')
                ->color('info'),

            Stat::make('Total Users', User::count())
                ->description('Total users in the system')
                ->icon('heroicon-o-user')
                ->color('danger'),
        ];
    }
}