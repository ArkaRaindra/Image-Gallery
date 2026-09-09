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
        
        // Mentions: @username (supports multi-word names with underscores)
        $html = preg_replace_callback(
            '/@([a-zA-Z0-9_]+)/',
            function ($matches) {
                $username = str_replace('_', ' ', $matches[1]);
                $user = \App\Models\User::where('name', $username)->first();
                if ($user) {
                    return '<a href="' . route('users.show', $user) . '" class="text-sky-700 font-semibold">@' . e($user->name) . '</a>';
                }
                return '<span class="text-sky-700 font-semibold">@' . e($matches[1]) . '</span>';
            },
            $html
        );

        // Bold: **text**
        $html = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html);

        // Italic: *text*
        $html = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $html);

        return nl2br($html);
    }
}