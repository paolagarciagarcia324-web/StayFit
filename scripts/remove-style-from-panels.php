<?php

$dirs = ['admin', 'cliente', 'coach', 'clienteIns'];
$root = dirname(__DIR__);

foreach ($dirs as $dir) {
    foreach (glob($root . '/views/' . $dir . '/*.php') as $file) {
        $content = file_get_contents($file);
        $updated = preg_replace(
            '/\s*<link rel="stylesheet" href="\.\.\/\.\.\/public\/style\.css"[^>]*>\s*/',
            "\n",
            $content
        );

        if ($updated !== $content) {
            file_put_contents($file, $updated);
            echo basename($file) . "\n";
        }
    }
}

echo "Done.\n";
