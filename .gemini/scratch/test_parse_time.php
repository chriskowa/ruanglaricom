<?php
function parseTimeToMs($timeInput): ?int
{
    if ($timeInput === null || $timeInput === '') {
        return null;
    }
    if (is_numeric($timeInput) && strpos((string) $timeInput, ':') === false) {
        return max(0, (int) $timeInput);
    }
    $str = trim((string) $timeInput);
    $parts = explode('.', str_replace(',', '.', $str));
    $timePart = $parts[0];
    $fractionMs = 0;
    if (isset($parts[1])) {
        $f = substr($parts[1], 0, 3);
        $fractionMs = (int) str_pad($f, 3, '0');
    }
    $t = explode(':', $timePart);
    if (count($t) === 3) {
        return ((int) $t[0] * 3600 + (int) $t[1] * 60 + (int) $t[2]) * 1000 + $fractionMs;
    }
    if (count($t) === 2) {
        return ((int) $t[0] * 60 + (int) $t[1]) * 1000 + $fractionMs;
    }
    return null;
}

echo 'HH:MM:SS -> ' . parseTimeToMs('01:23:45') . PHP_EOL;
echo 'HH:MM:SS.cs -> ' . parseTimeToMs('01:23:45.67') . PHP_EOL;
echo 'MM:SS.cs -> ' . parseTimeToMs('23:45.50') . PHP_EOL;
echo 'numeric ms -> ' . parseTimeToMs(1425500) . PHP_EOL;
