<?php

namespace App\Support;

/**
 * Thin line icons for the hero quiz tiles. The quiz options store an emoji (editable in Admin > Hero quiz); this maps the emoji we
 * ship to a minimal outline icon. An emoji with no match is shown as it is, so anything an admin types still works.
 */
class QuizIcons
{
    private const PATHS = [
        '🏘' => 'M3 20v-8l4-3 4 3v8M11 20v-6l4-3 6 4v5M3 20h18M6.5 15.5h1M15.5 16h1',
        '🏰' => 'M3 20V7l2 1.5L7 7v13M17 20V7l2 1.5L21 7v13M3 20h18M7 11h10M7 11V9l5-3.5L17 9v2M10.5 20v-3.5a1.5 1.5 0 0 1 3 0V20',
        '🌸' => 'M12 5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5ZM12 14a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5ZM7.5 9.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5ZM16.5 9.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5ZM12 10.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Z',
        '☀' => 'M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8ZM12 2.5v2M12 19.5v2M2.5 12h2M19.5 12h2M5.3 5.3l1.4 1.4M17.3 17.3l1.4 1.4M5.3 18.7l1.4-1.4M17.3 6.7l1.4-1.4',
        '🍂' => 'M5 19C5 10 10 5 19.5 4.5 19 14 14 19 5 19ZM5 19l8-8',
        '❄' => 'M12 3v18M4.2 7.5l15.6 9M4.2 16.5l15.6-9M9.5 4.5 12 6.5l2.5-2M9.5 19.5 12 17.5l2.5 2',
        '🚌' => 'M6 4.5h12A1.5 1.5 0 0 1 19.5 6v10.5h-15V6A1.5 1.5 0 0 1 6 4.5ZM4.5 11h15M5.5 16.5v2c0 .41.34.75.75.75h.5c.41 0 .75-.34.75-.75v-2m9.5 0v2c0 .41.34.75.75.75h.5c.41 0 .75-.34.75-.75v-2M7.5 13.75h.01M16.5 13.75h.01M8.5 7.75h7',
        '🚗' => 'M5 16.5V12l1.7-4.3A2 2 0 0 1 8.56 6.5h6.88a2 2 0 0 1 1.86 1.2L19 12v4.5M4 12h16M4 16.5h16M7 16.5v1.75c0 .41.34.75.75.75h.5c.41 0 .75-.34.75-.75V16.5m6 0v1.75c0 .41.34.75.75.75h.5c.41 0 .75-.34.75-.75V16.5M7.5 14.25h.01M16.5 14.25h.01',
        '🧭' => 'M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18ZM15.5 8.5l-2 5-5 2 2-5 5-2Z',
        '❓' => 'M9.5 9.2a2.7 2.7 0 0 1 5 1.3c0 1.8-2.5 2.2-2.5 3.7M12 17.5h.01M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18Z',
        '✈' => 'M21 12.5 14 9V4.5a2 2 0 0 0-4 0V9l-7 3.5V15l7-1.5V18l-2 1.5V21l4-1 4 1v-1.5L14 18v-4.5l7 1.5v-2.5Z',
        '🛫' => 'M3 20h18M4 14.5l-1-3.5h1.5l2 1.5 4.5-1.5-2.5-5h1.8l5 4.5 3.6-1.2a1.4 1.4 0 0 1 .9 2.7L7.5 15.7 4 14.5Z',
        '💵' => 'M3 7h18v10H3ZM12 9.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5ZM6.5 12h.01M17.5 12h.01',
        '💰' => 'M9 4h6l-1.5 3h-3L9 4ZM10.5 7C6 9 5 13 5.5 16.5 6 19 8.5 20.5 12 20.5s6-1.5 6.5-4C19 13 18 9 13.5 7M13.7 12.4c-.4-.5-1-.7-1.7-.7-1 0-1.7.5-1.7 1.3s.7 1.1 1.7 1.3 1.7.5 1.7 1.3-.7 1.3-1.7 1.3c-.7 0-1.3-.3-1.7-.8',
        '✅' => 'M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18ZM8 12.5l2.7 2.7L16 9.5',
        '👍' => 'M7 11v9H4v-9h3ZM7 11l3.5-7c1.5 0 2.5 1 2.5 2.5V9h5.2a1.8 1.8 0 0 1 1.8 2.1l-1.1 6.5a2.5 2.5 0 0 1-2.5 1.9H7',
        '🤔' => 'M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18ZM12 3v18M15.2 8h3M15.2 12h4.3M15.2 16h3',
        '🔎' => 'M10.5 4a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13ZM15.2 15.2 20 20',
    ];

    /** Inline SVG for an emoji, or null when we have no thin icon for it. */
    public static function svg(?string $emoji): ?string
    {
        $key = str_replace(["\u{FE0F}", "\u{FE0E}"], '', trim((string) $emoji));
        $d = self::PATHS[$key] ?? null;

        return $d ? '<svg style="width:2.75rem;height:2.75rem" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="'.$d.'"/></svg>' : null;
    }
}
