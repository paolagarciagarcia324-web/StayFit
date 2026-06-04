<?php

/**
 * Migra vistas de panel al sistema visual FigueFit premium.
 * Uso: php scripts/migrate-panel-views.php
 */

$root = dirname(__DIR__);
$dirs = ['admin', 'cliente', 'coach', 'clienteIns'];
$skip = ['dashboard.php'];

$layoutCssPatterns = [
    '/body\s*\{[^}]*\}/s',
    '/\.(admin-wrapper|cliente-wrapper|coach-wrapper|layout-wrapper)\s*\{[^}]*\}/s',
    '/\.sidebar\s*\{[^}]*\}/s',
    '/\.sidebar\s+h2\s*\{[^}]*\}/s',
    '/\.sidebar\s+a\s*\{[^}]*\}/s',
    '/\.sidebar\s+a:hover[^}]*\{[^}]*\}/s',
    '/\.sidebar\s+a\.active[^}]*\{[^}]*\}/s',
    '/\.content\s*\{[^}]*\}/s',
    '/\.hero\s*\{[^}]*\}/s',
    '/\.hero\s+h1\s*\{[^}]*\}/s',
    '/\.page-header\s*\{[^}]*\}/s',
    '/\.page-header\s+h1\s*\{[^}]*\}/s',
    '/\.stats\s*\{[^}]*\}/s',
    '/\.stat-card\s*\{[^}]*\}/s',
    '/\.stat-card\s+(small|h2|span|p)\s*\{[^}]*\}/s',
    '/\.grid\s*\{[^}]*\}/s',
    '/\.card\s*\{[^}]*\}/s',
    '/\.card\s+h3\s*\{[^}]*\}/s',
    '/\.timeline-item\s*\{[^}]*\}/s',
    '/\.item\s*\{[^}]*\}/s',
    '/\.badge\s*\{[^}]*\}/s',
    '/\.badge-alert\s*\{[^}]*\}/s',
    '/\.badge\.off\s*\{[^}]*\}/s',
    '/\.btn\s*\{[^}]*\}/s',
    '/\.btn-green\s*\{[^}]*\}/s',
    '/\.btn-dark\s*\{[^}]*\}/s',
    '/\.alert-box\s*\{[^}]*\}/s',
    '/\.topbar\s*\{[^}]*\}/s',
    '/\.topbar\s+strong\s*\{[^}]*\}/s',
    '/\.logout\s*\{[^}]*\}/s',
    '/label\s*\{[^}]*\}/s',
    '/input,\s*select\s*\{[^}]*\}/s',
    '/button,\s*\.btn\s*\{[^}]*\}/s',
    '/table\s*\{[^}]*\}/s',
    '/th\s*\{[^}]*\}/s',
    '/td\s*\{[^}]*\}/s',
    '/@media\s*\(max-width:\s*(900|1000)px\)\s*\{[^}]*(\{[^}]*\})*[^}]*\}/s',
];

$sidebarMap = [
    'admin'      => 'sidebarAdmin.php',
    'cliente'    => 'sidebarCliente.php',
    'coach'      => 'sidebarCoach.php',
    'clienteIns' => 'sidebarClienteIns.php',
];

$updated = 0;

foreach ($dirs as $dir) {
    $path = $root . '/views/' . $dir;
    if (!is_dir($path)) {
        continue;
    }

    foreach (glob($path . '/*.php') as $file) {
        $basename = basename($file);
        if (in_array($basename, $skip, true)) {
            continue;
        }

        $content = file_get_contents($file);
        if ($content === false || strpos($content, 'panel.css') !== false) {
            continue;
        }

        // panel.css link
        $content = preg_replace(
            '/(<title>[^<]+<\/title>)/i',
            '$1' . "\n    <link rel=\"stylesheet\" href=\"../../public/panel.css?v=1\">",
            $content,
            1
        );

        // body class
        $content = preg_replace('/<body>/', '<body class="fp-panel">', $content, 1);
        $content = preg_replace('/<body\s+class="([^"]*)">/', '<body class="fp-panel $1">', $content, 1);

        // Strip layout CSS from inline style blocks
        if (preg_match('/<style>(.*?)<\/style>/s', $content, $m)) {
            $css = $m[1];
            foreach ($layoutCssPatterns as $pattern) {
                $css = preg_replace($pattern, '', $css);
            }
            $css = trim(preg_replace('/\n{3,}/', "\n\n", $css));
            if ($css === '') {
                $content = preg_replace('/\s*<style>\s*<\/style>\s*/s', "\n", $content, 1);
            } else {
                $content = preg_replace('/<style>.*?<\/style>/s', "<style>\n{$css}\n    </style>", $content, 1);
            }
        }

        // Replace old sidebar block with partial if present
        if (preg_match('/<aside class="sidebar">.*?<\/aside>/s', $content)) {
            $partial = $sidebarMap[$dir];
            $require = "<?php require __DIR__ . '/../partials/panel/{$partial}'; ?>";
            $content = preg_replace('/<aside class="sidebar">.*?<\/aside>/s', $require, $content, 1);
        }

        file_put_contents($file, $content);
        $updated++;
        echo "Updated: views/{$dir}/{$basename}\n";
    }
}

echo "\nDone. {$updated} files updated.\n";
