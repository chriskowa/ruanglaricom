<?php

function searchWord($dir, $word) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $file) {
        if ($file->isDir()) continue;
        $path = $file->getPathname();
        if (str_contains($path, 'node_modules') || str_contains($path, 'vendor') || str_contains($path, '.git')) continue;
        
        $content = file_get_contents($path);
        if (stripos($content, $word) !== false) {
            echo "Found in: $path\n";
            $lines = explode("\n", $content);
            foreach ($lines as $num => $line) {
                if (stripos($line, $word) !== false) {
                    echo "  Line " . ($num + 1) . ": " . trim($line) . "\n";
                }
            }
        }
    }
}

echo "Searching for 'overstride'...\n";
searchWord('c:\laragon\www\ruanglari\resources', 'overstride');
searchWord('c:\laragon\www\ruanglari\app', 'overstride');

echo "\nSearching for 'over_stride'...\n";
searchWord('c:\laragon\www\ruanglari\resources', 'over_stride');
searchWord('c:\laragon\www\ruanglari\app', 'over_stride');
