<?php

namespace App\Support;

class SimpleMarkdown
{
    public static function toHtml(string $text): string
    {
        $html = e($text);

        // Images: ![alt](url)
        $html = preg_replace(
            '/!\[([^\]]*)\]\((https?:\/\/[^\s)]+)\)/',
            '<img src="$2" alt="$1" class="max-w-full rounded my-1">',
            $html
        );

        //Links: [text](url)
        $html = preg_replace(
            '/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/',
            '<a href="$2" target="_blank" rel="noopener" class="text-sky-700 underline">$1</a>',
            $html
        );

        // Bold: **text**
        $html = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html);

        // Italic: *text*
        $html = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $html);

        return nl2br($html);
    }
}