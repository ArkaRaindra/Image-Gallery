<?php

namespace App\Support;

use Filament\Support\Colors\Color;

/**
 * Tag category colors, mirroring the public site
 * (post-sidebar.blade.php: red/green/purple/amber/sky 700).
 */
class TagColors
{
    /**
     * Tailwind 700 shades, the same ones the public pages use.
     *
     * @var array<string, string>
     */
    private const HEX = [
        'artist' => '#b91c1c',
        'copyright' => '#7e22ce',
        'character' => '#15803d',
        'general' => '#0369a1',
        'meta' => '#b45309',
    ];

    /**
     * Hex color for places that need a raw color (charts, inline styles).
     */
    public static function hex(?string $category): string
    {
        return self::HEX[$category] ?? self::HEX['general'];
    }

    /**
     * Color palette for Filament's ->color() on columns and badges.
     *
     * Filament draws light-mode text with the 600 shade, so 600 is swapped
     * for the 700 shade to match the public site exactly. Dark mode keeps
     * Filament's own lighter shade so it stays readable.
     *
     * @return array<int, string>
     */
    public static function filament(?string $category): array
    {
        $palette = match ($category) {
            'artist' => Color::Red,
            'character' => Color::Green,
            'copyright' => Color::Purple,
            'meta' => Color::Amber,
            default => Color::Sky,
        };

        $palette[600] = $palette[700];

        return $palette;
    }
}