<?php
$source1 = 'C:\Users\LENOVO\.gemini\antigravity-ide\brain\669df828-a6db-40a6-9f81-643033898b85\indonesian_runner_sunrise_1789193510502.jpg';
$dest1 = __DIR__ . '/images/hero/indonesian-runner-sunrise.jpg';

$source2 = 'C:\Users\LENOVO\.gemini\antigravity-ide\brain\669df828-a6db-40a6-9f81-643033898b85\runner_finish_emotion_1789193533949.jpg';
$dest2 = __DIR__ . '/images/testimonial/runner-finish-emotion.jpg';

if (file_exists($source1)) {
    copy($source1, $dest1);
    echo "Copied image 1 successfully.<br>";
} else {
    echo "Source 1 not found.<br>";
}

if (file_exists($source2)) {
    copy($source2, $dest2);
    echo "Copied image 2 successfully.<br>";
} else {
    echo "Source 2 not found.<br>";
}
unlink(__FILE__);
