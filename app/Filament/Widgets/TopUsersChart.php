<?php

namespace App\Filament\Widgets;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Filament\Widgets\ChartWidget;

class TopUsersChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Top Users by Posts & Comments';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];

    protected ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $posts = Post::withoutGlobalScopes()
            ->whereNotNull('uploader_id')
            ->selectRaw('uploader_id as user_id, COUNT(*) as total')
            ->groupBy('uploader_id')
            ->pluck('total', 'user_id')
            ->map(fn ($total) => (int) $total);

        $comments = Comment::withoutGlobalScopes()
            ->whereNotNull('user_id')
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id')
            ->map(fn ($total) => (int) $total);

        // Rank by posts + comments combined, highest first.
        $topIds = $posts->keys()
            ->merge($comments->keys())
            ->unique()
            ->sortByDesc(fn ($id) => ($posts[$id] ?? 0) + ($comments[$id] ?? 0))
            ->take(5)
            ->values();

        $users = User::whereIn('id', $topIds)->pluck('name', 'id');

        return [
            'datasets' => [
                [
                    'label' => 'Posts',
                    'data' => $topIds->map(fn ($id) => $posts[$id] ?? 0)->all(),
                    'backgroundColor' => '#f59e0b',
                    'hoverBackgroundColor' => '#d97706',
                    'hoverBorderColor' => '#92400e',
                    'hoverBorderWidth' => 2,
                    'borderRadius' => 6,
                ],
                [
                    'label' => 'Comments',
                    'data' => $topIds->map(fn ($id) => $comments[$id] ?? 0)->all(),
                    'backgroundColor' => '#0ea5e9',
                    'hoverBackgroundColor' => '#0284c7',
                    'hoverBorderColor' => '#075985',
                    'hoverBorderWidth' => 2,
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $topIds->map(fn ($id) => $users[$id] ?? 'Unknown')->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'transitions' => [
                'active' => [
                    'animation' => ['duration' => 250],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                ],
            ],
        ];
    }
}