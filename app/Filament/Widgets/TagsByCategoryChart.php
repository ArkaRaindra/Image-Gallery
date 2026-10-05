<?php

namespace App\Filament\Widgets;

use App\Models\Tag;
use App\Support\TagColors;
use Filament\Widgets\ChartWidget;

class TagsByCategoryChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Tags by Category';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];

    protected ?string $maxHeight = '300px';

    /**
     * Same order as the tag sidebar on the public site.
     *
     * @var list<string>
     */
    private const CATEGORIES = ['artist', 'copyright', 'character', 'general', 'meta'];

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $counts = Tag::query()
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        $labels = [];
        $data = [];
        $colors = [];

        foreach (self::CATEGORIES as $category) {
            $labels[] = ucfirst($category);
            $data[] = (int) ($counts[$category] ?? 0);
            $colors[] = TagColors::hex($category);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Tags',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    // The hovered slice slides outward.
                    'hoverOffset' => 14,
                    'hoverBorderWidth' => 3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            // Padding keeps the slice from being clipped when it moves out.
            'layout' => [
                'padding' => 16,
            ],
            'transitions' => [
                'active' => [
                    'animation' => ['duration' => 250],
                ],
            ],
            'scales' => [
                'x' => ['display' => false],
                'y' => ['display' => false],
            ],
        ];
    }
}