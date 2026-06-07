<?php
$fontDir = __DIR__ . '/storage/app/public/fonts';
$urls = [
    'BebasNeue-Regular.ttf' => 'https://raw.githubusercontent.com/dharmatype/Bebas-Neue/master/fonts/BebasNeue(2014)ByDharmaType/ttf/BebasNeue-Regular.ttf',
    'Oswald-Bold.ttf' => 'https://raw.githubusercontent.com/vernnobile/OswaldFont/master/fonts/ttf/Oswald-Bold.ttf'
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
