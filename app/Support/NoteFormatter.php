<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

class NoteFormatter
{
    protected const ALLOWED_TAGS = [
        'b', 'i', 's', 'u', 'big', 'small', 'code', 'h1', 'tn',
        'span', 'div', 'a', 'ruby', 'rb', 'rt', 'ul', 'ol', 'li', 'br', 'p',
    ];

    protected const DROP_ENTIRELY = [
        'script', 'style', 'iframe', 'object', 'embed', 'link', 'meta',
        'form', 'input', 'textarea', 'button', 'svg', 'math',
    ];

    protected const ALLOWED_STYLE_PROPERTIES = [
        'align-items', 'background-clip', '-webkit-background-clip', 'background-color', 'background',
        'border', 'border-color', 'border-image', 'border-radius', 'border-style', 'border-width',
        'border-bottom', 'border-bottom-color', 'border-bottom-left-radius', 'border-bottom-right-radius',
        'border-bottom-style', 'border-bottom-width',
        'border-left', 'border-left-color', 'border-left-style', 'border-left-width',
        'border-right', 'border-right-color', 'border-right-style', 'border-right-width',
        'border-top', 'border-top-color', 'border-top-left-radius', 'border-top-right-radius',
        'border-top-style', 'border-top-width',
        'bottom', 'left', 'right', 'top',
        'box-shadow', 'display', 'filter', 'float',
        'font', 'font-family', 'font-size', 'font-size-adjust', 'font-style', 'font-variant', 'font-weight',
        'height', 'width', 'justify-content', 'letter-spacing', 'line-height', 'opacity',
        'perspective', 'perspective-origin',
        'text-align', 'text-decoration', 'text-indent', 'text-shadow', 'text-transform',
        'transform', 'transform-origin',
        '-webkit-text-fill-color', '-webkit-text-stroke', '-webkit-text-stroke-color', '-webkit-text-stroke-width',
        'white-space', 'word-break', 'word-spacing', 'word-wrap', 'overflow-wrap', 'writing-mode', 'vertical-align',
        'color', 'background-color', 'padding', 'padding-top', 'padding-bottom', 'padding-left', 'padding-right',
        'margin', 'margin-top', 'margin-bottom', 'margin-left', 'margin-right',
    ];

    protected const BLOCKED_VALUE_SUBSTRINGS = ['expression', 'javascript:', 'url(', '@import', '</', '<script'];

    public static function sanitize(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $document = new DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="utf-8"?><div id="note-root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $root = $document->getElementById('note-root');

        if (! $root) {
            return e($html);
        }

        static::cleanChildren($root);

        $inner = '';
        foreach ($root->childNodes as $child) {
            $inner .= $document->saveHTML($child);
        }

        return $inner;
    }

    protected static function cleanChildren(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMText) {
                continue;
            }

            if (! $child instanceof DOMElement) {
                $node->removeChild($child);
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, static::DROP_ENTIRELY, true)) {
                $node->removeChild($child);
                continue;
            }

            if (! in_array($tag, static::ALLOWED_TAGS, true)) {
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            static::cleanAttributes($child);
            static::cleanChildren($child);
        }
    }

    protected static function cleanAttributes(DOMElement $element): void
    {
        $tag = strtolower($element->tagName);
        $toRemove = [];

        foreach (iterator_to_array($element->attributes) as $attr) {
            $name = strtolower($attr->name);

            if ($name === 'style') {
                $clean = static::sanitizeStyle($attr->value);
                $clean === '' ? $toRemove[] = $attr->name : $element->setAttribute('style', $clean);
                continue;
            }

            if ($name === 'href' && $tag === 'a') {
                if (! static::isSafeHref($attr->value)) {
                    $toRemove[] = $attr->name;
                }
                continue;
            }

            $toRemove[] = $attr->name;
        }

        foreach ($toRemove as $name) {
            $element->removeAttribute($name);
        }

        if ($tag === 'a' && $element->hasAttribute('href')) {
            $element->setAttribute('target', '_blank');
            $element->setAttribute('rel', 'noopener noreferrer nofollow');
        }
    }

    protected static function isSafeHref(string $href): bool
    {
        return (bool) preg_match('#^(https?://|/)#i', trim($href));
    }

    protected static function sanitizeStyle(string $style): string
    {
        $kept = [];

        foreach (explode(';', $style) as $declaration) {
            if (! str_contains($declaration, ':')) {
                continue;
            }

            [$property, $value] = explode(':', $declaration, 2);
            $property = strtolower(trim($property));
            $value = trim($value);

            if ($property === '' || $value === '' || ! in_array($property, static::ALLOWED_STYLE_PROPERTIES, true)) {
                continue;
            }

            $lowerValue = strtolower($value);
            $blocked = false;
            foreach (static::BLOCKED_VALUE_SUBSTRINGS as $bad) {
                if (str_contains($lowerValue, $bad)) {
                    $blocked = true;
                    break;
                }
            }

            if (! $blocked) {
                $kept[] = "{$property}: {$value}";
            }
        }

        return implode('; ', $kept);
    }
}