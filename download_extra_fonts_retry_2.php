<?php
$fontDir = __DIR__ . '/storage/app/public/fonts';
$urls = [
    'BebasNeue-Regular.ttf' => 'https://raw.githubusercontent.com/google/fonts/main/ofl/bebasneue/BebasNeue-Regular.ttf',
    'Oswald-Bold.ttf' => 'https://raw.githubusercontent.com/google/fonts/main/ofl/oswald/static/Oswald-Bold.ttf'
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
