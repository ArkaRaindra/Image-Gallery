<?php

namespace App\Filament\Widgets;

use App\Models\Comment;
use App\Models\Post;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class PostsCommentsPerDayChart extends ChartWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Posts & Comments per Day';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return [
            '7' => 'Last 7 days',
            '30' => 'Last 30 days',
            '90' => 'Last 90 days',
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $days = (int) ($this->filter ?: 30);

        $start = now()->subDays($days - 1)->startOfDay();
        $end = now()->endOfDay();

        $posts = $this->countPerDay(Post::withoutGlobalScopes(), $start, $end);
        $comments = $this->countPerDay(Comment::withoutGlobalScopes(), $start, $end);

        $labels = [];
        $postData = [];
        $commentData = [];

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $key = $date->toDateString();

            $labels[] = $date->format('d M');
            $postData[] = $posts[$key] ?? 0;
            $commentData[] = $comments[$key] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Posts',
                    'data' => $postData,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'tension' => 0.3,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 8,
                    'pointHoverBorderWidth' => 3,
                    'pointHoverBackgroundColor' => '#ffffff',
                    'pointHoverBorderColor' => '#f59e0b',
                ],
                [
                    'label' => 'Comments',
                    'data' => $commentData,
                    'borderColor' => '#0ea5e9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.15)',
                    'tension' => 0.3,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 8,
                    'pointHoverBorderWidth' => 3,
                    'pointHoverBackgroundColor' => '#ffffff',
                    'pointHoverBorderColor' => '#0ea5e9',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            // Hovering anywhere on a day highlights both points of that day.
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
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

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return array<string, int>
     */
    private function countPerDay(Builder $query, $start, $end): array
    {
        return $query
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->map(fn ($total) => (int) $total)
            ->all();
    }
}