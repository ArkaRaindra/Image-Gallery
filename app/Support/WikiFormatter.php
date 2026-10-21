<?php

namespace App\Support;

use App\Models\WikiPage;

class WikiFormatter
{
    /**
     * Turn wiki text into safe HTML.
     *
     * Supported: "h1." to "h6." headings, "* " list items (more asterisks =
     * deeper), [[page title]] and [[page title|label]] wiki links, [label](https://...)
     * links, **bold** and *italic*. Everything else is escaped.
     */
    public static function toHtml(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $existing = self::existingTitles($text);

        $blocks = [];
        $paragraph = [];
        $items = [];

        foreach (explode("\n", $text) as $line) {
            $line = rtrim($line);

            if (preg_match('/^h([1-6])\.\s*(.+)$/', $line, $match)) {
                self::flush($blocks, $paragraph, $items, $existing);

                $level = (int) $match[1];
                $blocks[] = '<h'.$level.' class="'.self::headingClass($level).'">'
                    .self::inline($match[2], $existing).'</h'.$level.'>';

                continue;
            }

            if (preg_match('/^(\*+)\s+(.+)$/', $line, $match)) {
                self::flushParagraph($blocks, $paragraph, $existing);

                $items[] = [strlen($match[1]), $match[2]];

                continue;
            }

            if (trim($line) === '') {
                self::flush($blocks, $paragraph, $items, $existing);

                continue;
            }

            self::flushItems($blocks, $items, $existing);

            $paragraph[] = $line;
        }

        self::flush($blocks, $paragraph, $items, $existing);

        return implode("\n", $blocks);
    }

    /**
     * Titles of the [[links]] in the text that already have a wiki page, as a
     * set (title => true), so missing pages can be shown differently.
     *
     * @return array<string, true>
     */
    private static function existingTitles(string $text): array
    {
        preg_match_all('/\[\[([^\[\]|]+)(?:\|[^\[\]]*)?\]\]/', $text, $matches);

        $titles = collect($matches[1])
            ->map(fn (string $title) => WikiPage::normalizeTitle($title))
            ->filter()
            ->unique()
            ->values();

        if ($titles->isEmpty()) {
            return [];
        }

        return array_fill_keys(
            WikiPage::query()->whereIn('title', $titles->all())->pluck('title')->all(),
            true,
        );
    }

    /**
     * @param  list<string>  $blocks
     * @param  list<string>  $paragraph
     * @param  list<array{0: int, 1: string}>  $items
     * @param  array<string, true>  $existing
     */
    private static function flush(array &$blocks, array &$paragraph, array &$items, array $existing): void
    {
        self::flushParagraph($blocks, $paragraph, $existing);
        self::flushItems($blocks, $items, $existing);
    }

    /**
     * @param  list<string>  $blocks
     * @param  list<string>  $paragraph
     * @param  array<string, true>  $existing
     */
    private static function flushParagraph(array &$blocks, array &$paragraph, array $existing): void
    {
        if ($paragraph === []) {
            return;
        }

        $lines = array_map(fn (string $line) => self::inline($line, $existing), $paragraph);
        $blocks[] = '<p class="mb-2">'.implode('<br>', $lines).'</p>';
        $paragraph = [];
    }

    /**
     * @param  list<string>  $blocks
     * @param  list<array{0: int, 1: string}>  $items
     * @param  array<string, true>  $existing
     */
    private static function flushItems(array &$blocks, array &$items, array $existing): void
    {
        if ($items === []) {
            return;
        }

        $html = '<ul class="list-disc pl-6 mb-2">';

        foreach ($items as [$depth, $content]) {
            $indent = ($depth - 1) * 1.25;
            $html .= '<li style="margin-left: '.$indent.'rem">'.self::inline($content, $existing).'</li>';
        }

        $blocks[] = $html.'</ul>';
        $items = [];
    }

    private static function headingClass(int $level): string
    {
        return match ($level) {
            1 => 'text-2xl font-bold mt-4 mb-2',
            2 => 'text-xl font-bold mt-4 mb-2 border-b border-gray-700 pb-1',
            3 => 'text-lg font-bold mt-3 mb-1',
            4 => 'text-base font-bold mt-3 mb-1',
            default => 'text-sm font-bold mt-2 mb-1',
        };
    }

    /**
     * @param  array<string, true>  $existing
     */
    private static function inline(string $text, array $existing): string
    {
        $html = e($text);

        // External links: [label](https://...)
        $html = preg_replace(
            '/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/',
            '<a href="$2" target="_blank" rel="noopener" class="text-sky-700 hover:underline">$1</a>',
            $html
        );

        // Wiki links: [[title]] or [[title|label]]
        $html = preg_replace_callback(
            '/\[\[([^\[\]|]+)(?:\|([^\[\]]+))?\]\]/',
            function (array $match) use ($existing): string {
                $title = WikiPage::normalizeTitle(html_entity_decode(trim($match[1]), ENT_QUOTES));

                if ($title === '') {
                    return $match[0];
                }

                $label = isset($match[2]) && trim($match[2]) !== '' ? trim($match[2]) : trim($match[1]);

                if (isset($existing[$title])) {
                    $url = route('wiki.show', $title);
                    $class = 'text-sky-700 hover:underline';
                } else {
                    $url = route('wiki.create', ['title' => $title]);
                    $class = 'text-red-700 hover:underline';
                }

                return '<a href="'.e($url).'" class="'.$class.'">'.$label.'</a>';
            },
            $html
        );

        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);

        return preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);
    }
}