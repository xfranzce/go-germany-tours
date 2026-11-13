<?php

namespace App\Support;

/** Allow-list HTML sanitiser for admin rich text (Trix output). Everything else is removed. */
class Html
{
    private const ALLOWED = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li', 'h2', 'h3', 'h4', 'a', 'blockquote', 'div', 'del'];

    public static function clean(?string $html): ?string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return null;
        }

        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><body>'.$html.'</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $body = $doc->getElementsByTagName('body')->item(0);
        if (! $body) {
            return strip_tags($html);
        }
        self::walk($body);

        $out = '';
        foreach ($body->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return trim($out) ?: null;
    }

    private static function walk(\DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof \DOMComment) {
                $node->removeChild($child);

                continue;
            }
            if (! $child instanceof \DOMElement) {
                continue;
            }
            $tag = strtolower($child->tagName);

            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form', 'svg', 'math'], true)) {
                $node->removeChild($child);

                continue;
            }

            self::walk($child);

            if ($tag === 'h1') { // Trix headings: the page already has an H1
                $h2 = $child->ownerDocument->createElement('h2');
                while ($child->firstChild) {
                    $h2->appendChild($child->firstChild);
                }
                $node->replaceChild($h2, $child);
                $child = $h2;
                $tag = 'h2';
            }

            if (! in_array($tag, self::ALLOWED, true)) {
                // unwrap: keep the text/children, drop the element
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attr) {
                $keep = $tag === 'a' && in_array($attr->name, ['href', 'target', 'rel'], true);
                if ($keep && $attr->name === 'href' && ! preg_match('~^(https?://|mailto:|tel:|/|#)~i', trim($attr->value))) {
                    $keep = false;
                }
                if (! $keep) {
                    $child->removeAttribute($attr->name);
                }
            }
            if ($tag === 'a' && $child->hasAttribute('target')) {
                $child->setAttribute('rel', 'noopener');
            }
        }
    }
}
