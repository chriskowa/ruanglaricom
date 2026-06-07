<?php
$fontDir = __DIR__ . '/storage/app/public/fonts';
if (!is_dir($fontDir)) {
    mkdir($fontDir, 0777, true);
}

$urls = [
    'Montserrat-ExtraBold.ttf' => 'https://github.com/JulietaUla/Montserrat/raw/master/fonts/ttf/Montserrat-ExtraBold.ttf',
    'BebasNeue-Regular.ttf' => 'https://github.com/googlefonts/bebas-neue/raw/main/fonts/ttf/BebasNeue-Regular.ttf',
    'Oswald-Bold.ttf' => 'https://github.com/googlefonts/oswald/raw/main/fonts/ttf/Oswald-Bold.ttf'
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
