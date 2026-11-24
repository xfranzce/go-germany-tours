<?php

namespace App\Support;

/**
 * Detailed line-art icons for the hero quiz tiles. The quiz options store an emoji (editable in Admin > Hero quiz); this maps the emoji we
 * ship to an outline icon. An emoji with no match is shown as it is, so anything an admin types still works.
 */
class QuizIcons
{
    /** Detailed line-art icons drawn on a 48 grid. Raw SVG shapes, stroke only. */
    private static function shapes(): array
    {
        $flower = function (float $cx, float $cy, float $k): string {
            $o = '';
            for ($a = 0; $a < 360; $a += 60) {
                $o .= '<ellipse cx="'.$cx.'" cy="'.($cy - 4.2 * $k).'" rx="'.(2.2 * $k).'" ry="'.(3.2 * $k).'" transform="rotate('.$a.' '.$cx.' '.$cy.')"/>';
            }

            return $o.'<circle cx="'.$cx.'" cy="'.$cy.'" r="'.(1.5 * $k).'"/>';
        };
        $arms = '';
        foreach ([0, 60, 120] as $a) {
            $arms .= '<path transform="rotate('.$a.' 24 24)" d="M24 4v40M24 9.5l-4-3.5M24 9.5l4-3.5M24 38.5l-4 3.5M24 38.5l4 3.5M24 15.5l-3-2.7M24 15.5l3-2.7M24 32.5l-3 2.7M24 32.5l3 2.7"/>';
        }

        return [
            // spring: bouquet of three flowers
            '🌸' => $flower(24, 11, 1).$flower(12.5, 21, .8).$flower(35.5, 21, .8)
                .'<path d="M24 17v25M12.5 26.5c0 7 8 9 11.5 15.5M35.5 26.5c0 7-8 9-11.5 15.5M24 34c-5.5-.5-8-3.5-8-7.5 5.5 0 8 3.5 8 7.5ZM24 34c5.5-.5 8-3.5 8-7.5-5.5 0-8 3.5-8 7.5ZM20 43.5h8"/>',
            // summer: beach umbrella, sun, sand and waves
            '☀' => '<path d="M5 25C5 15.5 11.5 9 19.5 9S34 15.5 34 25c-2.4-2.4-4.6-2.4-7 0-2.4-2.4-4.7-2.4-7.2 0-2.4-2.4-4.6-2.4-7 0-2.3-2.4-4.6-2.4-7.8 0ZM19.5 9v16M12.5 25c0-8 3-13.5 7-16M27 25c0-8-3-13.5-7.5-16M19.5 25l-3.5 15"/>'
                .'<circle cx="38" cy="9" r="3.6"/><path d="M38 1.6v2M38 14.4v2M30.6 9h2M43.4 9h2M32.8 3.8l1.4 1.4M41.8 12.8l1.4 1.4M32.8 14.2l1.4-1.4M41.8 5.2l1.4-1.4"/>'
                .'<path d="M4 38.5c3.5-3 7-3 10.5 0s7 3 10.5 0 7-3 10.5 0 5.5 2.4 8.5 0M4 44c3.5-3 7-3 10.5 0s7 3 10.5 0 7-3 10.5 0 5.5 2.4 8.5 0"/>',
            // autumn: maple leaf, two falling leaves, wind
            '🍂' => '<path d="M26 14l3 6 5-3-2 7 6 1-4 4 3 5-8-1-2 3-2-3-8 1 3-5-4-4 6-1-2-7 5 3Z"/><path d="M26 36v8M26 36V22M26 30l-6-4M26 30l6-4"/>'
                .'<path d="M7 13c0-4.5 4-7 9.5-6 0 4.5-3.5 7.5-9.5 6ZM7 13l6.5-3.5M27 8c0-3 2.5-4.5 6-4 0 3-2.2 5-6 4ZM27 8l4.2-2.3"/><path d="M3 22h9a2.5 2.5 0 1 0-2.5-2.5M3 28h6.5a2 2 0 1 1-2 2"/>',
            // winter: snowflake
            '❄' => $arms.'<path d="M24 20.5l3.1 1.75v3.5L24 27.5l-3.1-1.75v-3.5Z"/>',
            // destination
            '🏘' => '<path d="M2 42h44M4 42V27l8-8 8 8v15M2 28.5 12 18.5l10 10M9 32h6v5H9ZM12 32v5M9 34.5h6M20 42V23l9-12 9 12v19M25.5 28h7v6h-7ZM29 28v6M25.5 31h7M33 14.5V9h3v8.5M36 42V32.5l5.5-6 5.5 6V42M39 36h5v6h-5Z"/>',
            '🏰' => '<path d="M3 42h42M19 42V18h10v24M17.5 18 24 4l6.5 14ZM24 4V1.5l3.2 1.1L24 3.8M7 42V24h8v18M5.5 24 11 13l5.5 11ZM33 42V24h8v18M31.5 24 37 13l5.5 11ZM15 42V31h4M29 31h4M21.5 42v-6a2.5 2.5 0 0 1 5 0v6M23 24h2v4h-2ZM10 30h2v4h-2ZM36 30h2v4h-2ZM22.5 12h3"/>',
            // travel style
            '🚌' => '<path d="M3 36V15a2 2 0 0 1 2-2h31.5c3.5 0 6.2 2.5 7.2 6l1.8 7V36M3 36h6M18 36h11M39 36h6M3 31h42M8 17h8v8H8ZM19.5 17h8v8h-8ZM31 17h5.5c1.6 0 3 1 3.6 2.6L41 25H31Z"/><circle cx="13.5" cy="36" r="4.5"/><circle cx="34" cy="36" r="4.5"/><circle cx="13.5" cy="36" r="1.2"/><circle cx="34" cy="36" r="1.2"/>',
            '🚗' => '<path d="M8.5 34H4v-7c0-1.5 1-2.5 2.5-2.8L12 23l4-7c.6-1.2 1.8-2 3.2-2h10.5c1.3 0 2.5.6 3.3 1.7L38 24l4 .8c2 .4 3 1.6 3 3.6V34h-4.5M17.5 34h14M16.5 24l3-6.5h6.7l4.3 6.5ZM23.5 17.5V24M41.5 28h2M6.5 28H9"/><circle cx="13" cy="34" r="4.3"/><circle cx="36" cy="34" r="4.3"/><circle cx="13" cy="34" r="1.2"/><circle cx="36" cy="34" r="1.2"/>',
            '🧭' => '<path d="M24 6a18 18 0 1 0 0 36 18 18 0 0 0 0-36ZM24 10.5a13.5 13.5 0 1 0 0 27 13.5 13.5 0 0 0 0-27ZM24 6v3M24 39v3M6 24h3M39 24h3M30.5 17.5l-3.7 9.3-9.3 3.7 3.7-9.3ZM20.8 27.2l6-6M24 23a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z"/>',
            '❓' => '<path d="M8 8h32a3 3 0 0 1 3 3v20a3 3 0 0 1-3 3H23l-9 7.5V34H8a3 3 0 0 1-3-3V11a3 3 0 0 1 3-3ZM19.2 18.8a4.8 4.8 0 1 1 7.4 4c-1.6 1-2.6 2-2.6 3.8M24 30.2h.01"/>',
            // flights
            '✈' => '<g transform="translate(26 21) rotate(45) scale(1.45) translate(-12 -12)"><path stroke-width="1" d="M21 12.5 14 9V4.5a2 2 0 0 0-4 0V9l-7 3.5V15l7-1.5V18l-2 1.5V21l4-1 4 1v-1.5L14 18v-4.5l7 1.5v-2.5Z"/></g><path stroke-dasharray="1.5 3.5" d="M4 42c8 0 13-4 16-10"/>',
            '🛫' => '<g transform="scale(2)" stroke-width=".75"><path d="M3 19h18M4 14.5l-1-3.5h1.5l2 1.5 4.5-1.5-2.5-5h1.8l5 4.5 3.6-1.2a1.4 1.4 0 0 1 .9 2.7L7.5 15.7 4 14.5ZM6 21.5h2M11 21.5h2M16 21.5h2"/></g>',
            // budget
            '💵' => '<path d="M9 12V8h36v22h-4M3 12h38v24H3ZM7.5 16.5h29v15h-29ZM22 20.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM24.3 23.3c-.6-.8-1.4-1.1-2.3-1.1-1.3 0-2.2.7-2.2 1.7s1 1.4 2.2 1.6 2.2.6 2.2 1.7-.9 1.7-2.2 1.7c-.9 0-1.8-.4-2.3-1.2M11 24h.01M33 24h.01"/>',
            '💰' => '<path d="M16.5 8c1-2.5 3.5-3.5 5-2.2 1.5-1.5 4.5-1.5 6 0 1.5-1.3 4-.3 5 2.2l-3 7H19.5ZM19.5 15C9.5 20 6.5 28 8 36c1 5.5 7 8.5 16 8.5S39 41.5 40 36c1.5-8-1.5-16-11.5-21M24 22.5v18M29.5 27.5c-1-1.6-2.8-2.4-5.5-2.4-3.2 0-5 1.4-5 3.6s2 3.2 5 3.8 5.2 1.3 5.2 3.8-2.1 3.8-5.4 3.8c-2.5 0-4.4-.9-5.5-2.6"/>',
            // likelihood
            '✅' => '<path d="M24 8a12.5 12.5 0 1 0 0 25 12.5 12.5 0 0 0 0-25ZM24 12a8.5 8.5 0 1 0 0 17 8.5 8.5 0 0 0 0-17ZM18.8 20.8l3.6 3.8 7-7.6M17.5 31.5 13.5 44l6-3 3.5 4.5 2-10.5M30.5 31.5 34.5 44l-6-3"/>',
            '👍' => '<path d="M5 22h8v21H5ZM13 24.5l7.5-15c2.6 0 4.5 2 4.5 4.6V21h12.5a3.5 3.5 0 0 1 3.4 4.3l-3 14A4.5 4.5 0 0 1 33.5 43H13M25.5 27h9.5M24.5 32h9M23.5 37h8"/>',
            '🤔' => '<path d="M24 10.5V41M15.5 41h17M24 4.5a3 3 0 1 0 0 6 3 3 0 0 0 0-6ZM8 14h32M8 14l-5 14M8 14l5 14M3 28h10a5 5 0 0 1-10 0ZM40 14l-5 14M40 14l5 14M35 28h10a5 5 0 0 1-10 0ZM11 41h26"/>',
            '🔎' => '<path d="M20 6a14 14 0 1 0 0 28 14 14 0 0 0 0-28ZM20 10a10 10 0 1 0 0 20 10 10 0 0 0 0-20ZM29.6 30.6l12 12a2.5 2.5 0 0 0 3.4-3.4l-12-12M17 17.5a3 3 0 1 1 4.2 2.7c-.9.5-1.2 1-1.2 2M20 26h.01"/>',
        ];
    }

    /** Inline SVG for an emoji, or null when we have no thin icon for it. */
    public static function svg(?string $emoji): ?string
    {
        $key = str_replace(["\u{FE0F}", "\u{FE0E}"], '', trim((string) $emoji));
        $attrs = 'style="width:2.75rem;height:2.75rem" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"';

        if ($shape = self::shapes()[$key] ?? null) {
            return '<svg '.$attrs.' viewBox="0 0 48 48" stroke-width="1.5">'.$shape.'</svg>';
        }

        return null;
    }
}
