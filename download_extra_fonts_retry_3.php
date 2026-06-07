<?php
$fontDir = __DIR__ . '/storage/app/public/fonts';
$urls = [
    'Montserrat-ExtraBold.ttf' => 'https://raw.githubusercontent.com/JulietaUla/Montserrat/master/fonts/ttf/Montserrat-ExtraBold.ttf',
    'Anton-Regular.ttf' => 'https://raw.githubusercontent.com/google/fonts/main/ofl/anton/Anton-Regular.ttf'
];

foreach ($urls as $name => $url) {
    echo "Downloading $name...\n";
    $content = @file_get_contents($url);
    if ($content !== false) {
        file_put_contents("$fontDir/$name", $content);
    } else {
        echo "Failed to download $name\n";
    }
}
echo "Done.\n";
