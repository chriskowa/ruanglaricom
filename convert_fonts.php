<?php
require __DIR__ . '/vendor/autoload.php';

$fontDir = __DIR__ . '/storage/app/public/fonts';
$regular = TCPDF_FONTS::addTTFfont("$fontDir/Montserrat-Regular.ttf", 'TrueTypeUnicode', '', 96);
$bold = TCPDF_FONTS::addTTFfont("$fontDir/Montserrat-Bold.ttf", 'TrueTypeUnicode', '', 96);

echo "Regular: $regular\n";
echo "Bold: $bold\n";
