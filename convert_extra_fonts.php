<?php
require __DIR__ . '/vendor/autoload.php';

$fontDir = __DIR__ . '/storage/app/public/fonts';
$bebas = TCPDF_FONTS::addTTFfont("$fontDir/BebasNeue-Regular.ttf", 'TrueTypeUnicode', '', 96);
$montserratEb = TCPDF_FONTS::addTTFfont("$fontDir/Montserrat-ExtraBold.ttf", 'TrueTypeUnicode', '', 96);
$anton = TCPDF_FONTS::addTTFfont("$fontDir/Anton-Regular.ttf", 'TrueTypeUnicode', '', 96);

echo "Bebas: $bebas\n";
echo "Montserrat ExtraBold: $montserratEb\n";
echo "Anton: $anton\n";
