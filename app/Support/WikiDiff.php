<?php

namespace App\Support;

class WikiDiff
{
    /**
     * Above this many token pairs the exact comparison is skipped and the
     * changed middle part is shown as one removal followed by one addition.
     */
    private const MAX_CELLS = 2000000;

    /**
     * Word by word difference between two texts.
     *
     * Every segment is "same", "del" (only in the old text) or "add" (only in
     * the new text). Joining the "same" and "del" segments gives the old text,
     * joining the "same" and "add" segments gives the new text.
     *
     * @return list<array{type: string, text: string}>
     */
    public static function compute(string $old, string $new): array
    {
        $oldTokens = self::tokenize($old);
        $newTokens = self::tokenize($new);

        $oldCount = count($oldTokens);
        $newCount = count($newTokens);

        $start = 0;

        while ($start < $oldCount && $start < $newCount && $oldTokens[$start] === $newTokens[$start]) {
            $start++;
        }

        $oldEnd = $oldCount;
        $newEnd = $newCount;

        while ($oldEnd > $start && $newEnd > $start && $oldTokens[$oldEnd - 1] === $newTokens[$newEnd - 1]) {
            $oldEnd--;
            $newEnd--;
        }

        $segments = [];

        foreach (array_slice($oldTokens, 0, $start) as $token) {
            $segments[] = ['type' => 'same', 'text' => $token];
        }

        $middle = self::diffMiddle(
            array_slice($oldTokens, $start, $oldEnd - $start),
            array_slice($newTokens, $start, $newEnd - $start),
        );

        foreach ($middle as $segment) {
            $segments[] = $segment;
        }

        foreach (array_slice($oldTokens, $oldEnd) as $token) {
            $segments[] = ['type' => 'same', 'text' => $token];
        }

        return self::merge($segments);
    }

    /**
     * Words keep the whitespace that follows them, so joining the tokens
     * gives the original text back.
     *
     * @return list<string>
     */
    private static function tokenize(string $text): array
    {
        if ($text === '') {
            return [];
        }

        $tokens = preg_split('/(?<=\s)(?=\S)/u', $text);

        return $tokens === false ? [$text] : $tokens;
    }

    /**
     * @param  list<string>  $old
     * @param  list<string>  $new
     * @return list<array{type: string, text: string}>
     */
    private static function diffMiddle(array $old, array $new): array
    {
        $oldCount = count($old);
        $newCount = count($new);
        $segments = [];

        if ($oldCount === 0 || $newCount === 0 || $oldCount * $newCount > self::MAX_CELLS) {
            foreach ($old as $token) {
                $segments[] = ['type' => 'del', 'text' => $token];
            }

            foreach ($new as $token) {
                $segments[] = ['type' => 'add', 'text' => $token];
            }

            return $segments;
        }

        $lengths = array_fill(0, $oldCount + 1, array_fill(0, $newCount + 1, 0));

        for ($i = $oldCount - 1; $i >= 0; $i--) {
            for ($j = $newCount - 1; $j >= 0; $j--) {
                $lengths[$i][$j] = $old[$i] === $new[$j]
                    ? $lengths[$i + 1][$j + 1] + 1
                    : max($lengths[$i + 1][$j], $lengths[$i][$j + 1]);
            }
        }

        $i = 0;
        $j = 0;

        while ($i < $oldCount && $j < $newCount) {
            if ($old[$i] === $new[$j]) {
                $segments[] = ['type' => 'same', 'text' => $old[$i]];
                $i++;
                $j++;
            } elseif ($lengths[$i + 1][$j] >= $lengths[$i][$j + 1]) {
                $segments[] = ['type' => 'del', 'text' => $old[$i]];
                $i++;
            } else {
                $segments[] = ['type' => 'add', 'text' => $new[$j]];
                $j++;
            }
        }

        for (; $i < $oldCount; $i++) {
            $segments[] = ['type' => 'del', 'text' => $old[$i]];
        }

        for (; $j < $newCount; $j++) {
            $segments[] = ['type' => 'add', 'text' => $new[$j]];
        }

        return $segments;
    }

    /**
     * Join neighbouring segments of the same type into one.
     *
     * @param  list<array{type: string, text: string}>  $segments
     * @return list<array{type: string, text: string}>
     */
    private static function merge(array $segments): array
    {
        $merged = [];

        foreach ($segments as $segment) {
            $last = count($merged) - 1;

            if ($last >= 0 && $merged[$last]['type'] === $segment['type']) {
                $merged[$last]['text'] .= $segment['text'];
            } else {
                $merged[] = $segment;
            }
        }

        return $merged;
    }
}