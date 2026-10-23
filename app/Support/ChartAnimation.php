<?php

namespace App\Support;

/**
 * Chart.js animation options shared by the dashboard chart widgets.
 *
 * Clicking a legend item (the label that gets struck through) hides or shows
 * that dataset / slice. These options make that change glide instead of snap:
 * the slice sweeps shut (or open) and its neighbours resize with it, bars and
 * lines fade out (or in) while the axis rescales.
 */
class ChartAnimation
{
    /**
     * @param  int  $duration  Milliseconds for the hide / show / resize animation.
     * @param  array<string, mixed>  $animation  Extra `animation` options (e.g. animateRotate).
     * @return array<string, mixed>
     */
    public static function legendToggle(int $duration = 700, array $animation = []): array
    {
        return [
            'animation' => array_merge([
                'duration' => $duration,
                'easing' => 'easeInOutCubic',
            ], $animation),
            'transitions' => [
                // Legend click that brings a dataset back.
                'show' => [
                    'animations' => [
                        'colors' => ['from' => 'transparent', 'duration' => $duration],
                    ],
                ],
                // Legend click that strikes a dataset out.
                'hide' => [
                    'animations' => [
                        'colors' => ['to' => 'transparent', 'duration' => $duration],
                    ],
                ],
            ],
        ];
    }
}