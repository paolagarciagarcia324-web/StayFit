<?php

/**
 * Iconos SVG lineales para el dashboard admin (estilo premium, sin emojis).
 */
function dashIcon(string $nombre): string
{
    $stroke = 'stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" fill="none"';

    $iconos = [
        'users' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path ' . $stroke . ' d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle ' . $stroke . ' cx="9" cy="7" r="4"/><path ' . $stroke . ' d="M22 21v-2a4 4 0 0 0-3-3.87"/><path ' . $stroke . ' d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',

        'clipboard' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path ' . $stroke . ' d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect ' . $stroke . ' x="8" y="2" width="8" height="4" rx="1"/><path ' . $stroke . ' d="M9 12h6M9 16h6"/></svg>',

        'card' => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect ' . $stroke . ' x="2" y="5" width="20" height="14" rx="2"/><path ' . $stroke . ' d="M2 10h20"/></svg>',

        'play' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle ' . $stroke . ' cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8" fill="currentColor" stroke="none"/></svg>',

        'alert' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path ' . $stroke . ' d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path ' . $stroke . ' d="M12 9v4"/><circle cx="12" cy="17" r="1" fill="currentColor" stroke="none"/></svg>',

        'inbox' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path ' . $stroke . ' d="M22 12h-6l-2 3H10l-2-3H2"/><path ' . $stroke . ' d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>',

        'check' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle ' . $stroke . ' cx="12" cy="12" r="10"/><path ' . $stroke . ' d="m9 12 2 2 4-4"/></svg>',

        'user' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path ' . $stroke . ' d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle ' . $stroke . ' cx="12" cy="7" r="4"/></svg>',

        'link' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path ' . $stroke . ' d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path ' . $stroke . ' d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>',

        'package' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path ' . $stroke . ' d="m7.5 4.27 9 5.15"/><path ' . $stroke . ' d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path ' . $stroke . ' d="m3.3 7 8.7 5 8.7-5M12 22V12"/></svg>',

        'arrow' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path ' . $stroke . ' d="M5 12h14"/><path ' . $stroke . ' d="m12 5 7 7-7 7"/></svg>',
    ];

    return $iconos[$nombre] ?? '';
}
