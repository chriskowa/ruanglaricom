<?php
$fontDir = __DIR__ . '/storage/app/public/fonts';
if (!is_dir($fontDir)) {
    mkdir($fontDir, 0777, true);
}

$urls = [
    'Montserrat-Regular.ttf' => 'https://github.com/JulietaUla/Montserrat/raw/master/fonts/ttf/Montserrat-Regular.ttf',
    'Montserrat-Bold.ttf' => 'https://github.com/JulietaUla/Montserrat/raw/master/fonts/ttf/Montserrat-Bold.ttf'
];

foreach ($urls as $name => $url) {
    echo "Downloading $name...\n";
    file_put_contents("$fontDir/$name", file_get_contents($url));
}
echo "Done.\n";
