<?php

$root = dirname(__DIR__) . '/views';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

foreach ($it as $f) {
    if ($f->getExtension() !== 'php') {
        continue;
    }

    $path = $f->getPathname();
    $content = file_get_contents($path);
    $updated = $content;

    $updated = str_replace('class="fp-panel fp-panel"', 'class="fp-panel"', $updated);
    $updated = preg_replace('/<style>\s*button,\s*<\/style>\s*/', '', $updated);
    $updated = preg_replace('/\s*<style>\s*<\/style>\s*/', "\n", $updated);

    if ($updated !== $content) {
        file_put_contents($path, $updated);
        echo 'Fixed: ' . str_replace(dirname(__DIR__) . '/', '', $path) . "\n";
    }
}

echo "Done.\n";
