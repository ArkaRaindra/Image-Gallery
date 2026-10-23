<?php

namespace App\Support;

use App\Models\ArtistVersion;

/**
 * What a version of an artist changed compared with another version.
 *
 * Every entry has a type: "same", "add" (only in the new version), "del" (only
 * in the old version) or, for URLs, "status" (still there but its active flag
 * changed; "old" holds the previous text).
 */
class ArtistDiff
{
    /**
     * @return array{
     *     name: array{old: ?string, new: string},
     *     group: array{old: ?string, new: ?string, changed: bool},
     *     other_names: list<array{text: string, type: string}>,
     *     urls: list<array{text: string, type: string, old?: string}>
     * }
     */
    public static function compare(?ArtistVersion $base, ArtistVersion $version): array
    {
        $oldNames = $base?->otherNameList() ?? [];
        $newNames = $version->otherNameList();

        $names = [];

        foreach ($oldNames as $name) {
            if (! in_array($name, $newNames, true)) {
                $names[] = ['text' => $name, 'type' => 'del'];
            }
        }

        foreach ($newNames as $name) {
            $names[] = ['text' => $name, 'type' => in_array($name, $oldNames, true) ? 'same' : 'add'];
        }

        $oldUrls = $base?->urlMap() ?? [];
        $newUrls = $version->urlMap();

        $urls = [];

        foreach ($oldUrls as $url => $active) {
            if (! array_key_exists($url, $newUrls)) {
                $urls[] = ['text' => self::urlText($url, $active), 'type' => 'del'];
            }
        }

        foreach ($newUrls as $url => $active) {
            if (! array_key_exists($url, $oldUrls)) {
                $urls[] = ['text' => self::urlText($url, $active), 'type' => 'add'];
            } elseif ($oldUrls[$url] !== $active) {
                $urls[] = [
                    'text' => self::urlText($url, $active),
                    'old' => self::urlText($url, $oldUrls[$url]),
                    'type' => 'status',
                ];
            } else {
                $urls[] = ['text' => self::urlText($url, $active), 'type' => 'same'];
            }
        }

        $renamed = $base !== null && $base->name !== $version->name;
        $groupChanged = $base !== null && (string) $base->group_name !== (string) $version->group_name;

        return [
            'name' => ['old' => $renamed ? $base->name : null, 'new' => $version->name],
            'group' => ['old' => $base?->group_name, 'new' => $version->group_name, 'changed' => $groupChanged],
            'other_names' => $names,
            'urls' => $urls,
        ];
    }

    private static function urlText(string $url, bool $active): string
    {
        return ($active ? '' : '-').$url;
    }
}
