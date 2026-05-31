<?php
$content = file_get_contents('app/Http/Controllers/EO/EventController.php');
$lines = explode("\n", $content);
$found = false;
foreach ($lines as $i => $line) {
    if (strpos($line, 'public function getPublicEventDetailAnalytics(') !== false || strpos($line, 'protected function getPublicEventDetailAnalytics(') !== false) {
        $found = true;
        // Print around this line
        for ($j = max(0, $i - 2); $j <= min(count($lines) - 1, $i + 100); $j++) {
            echo "Line " . ($j + 1) . ": " . $lines[$j] . "\n";
        }
        break;
    }
}
if (!$found) echo "Not found\n";
