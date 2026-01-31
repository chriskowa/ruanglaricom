<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BenchmarkGpxParsing extends Command
{
    protected $signature = 'gpx:benchmark {public_path : Path di storage/public, contoh: master-gpx/file.gpx}';

    protected $description = 'Benchmark parsing GPX (streaming) untuk file besar.';

    public function handle(): int
    {
        $publicPath = (string) $this->argument('public_path');
        $disk = Storage::disk('public');

        if (! $disk->exists($publicPath)) {
            $this->error('File tidak ditemukan di storage/public: '.$publicPath);
            return self::FAILURE;
        }

        $fullPath = $disk->path($publicPath);
        $size = filesize($fullPath) ?: 0;

        $start = microtime(true);

        $reader = new \XMLReader();
        $ok = $reader->open($fullPath, null, LIBXML_NONET | LIBXML_COMPACT);
        if (! $ok) {
            $this->error('Gagal membuka file GPX.');
            return self::FAILURE;
        }

        $points = 0;
        $distanceKm = 0.0;
        $gain = 0.0;
        $loss = 0.0;
        $minEle = null;
        $maxEle = null;

        $prevLat = null;
        $prevLon = null;
        $prevEle = null;

        while ($reader->read()) {
            if ($reader->nodeType !== \XMLReader::ELEMENT) {
                continue;
            }

            if ($reader->localName !== 'trkpt' && $reader->localName !== 'rtept') {
                continue;
            }

            $lat = $reader->getAttribute('lat');
            $lon = $reader->getAttribute('lon');
            if ($lat === null || $lon === null) {
                continue;
            }

            $latF = (float) $lat;
            $lonF = (float) $lon;

            $ele = null;
            $depth = $reader->depth;
            while ($reader->read()) {
                if ($reader->nodeType === \XMLReader::END_ELEMENT && $reader->depth === $depth) {
                    break;
                }
                if ($reader->nodeType === \XMLReader::ELEMENT && $reader->localName === 'ele') {
                    $eleText = $reader->readString();
                    if ($eleText !== '') {
                        $ele = (float) $eleText;
                    }
                }
            }

            $points++;

            if ($prevLat !== null && $prevLon !== null) {
                $distanceKm += $this->haversineKm($prevLat, $prevLon, $latF, $lonF);
                if ($ele !== null && $prevEle !== null) {
                    $d = $ele - $prevEle;
                    if ($d > 0) $gain += $d;
                    if ($d < 0) $loss += abs($d);
                }
            }

            if ($ele !== null) {
                $minEle = ($minEle === null) ? $ele : min($minEle, $ele);
                $maxEle = ($maxEle === null) ? $ele : max($maxEle, $ele);
            }

            $prevLat = $latF;
            $prevLon = $lonF;
            $prevEle = $ele;
        }

        $reader->close();

        $elapsed = microtime(true) - $start;
        $peak = memory_get_peak_usage(true);

        $this->info('File: '.$publicPath);
        $this->info('Size: '.round($size / 1024 / 1024, 2).' MB');
        $this->info('Points: '.$points);
        $this->info('Distance: '.round($distanceKm, 3).' km');
        $this->info('Elev Gain/Loss: +'.(int) round($gain).' / -'.(int) round($loss).' m');
        $this->info('Min/Max: '.(($minEle === null) ? '-' : (int) round($minEle)).' / '.(($maxEle === null) ? '-' : (int) round($maxEle)).' m');
        $this->info('Time: '.round($elapsed, 3).' s');
        $this->info('Peak memory: '.round($peak / 1024 / 1024, 2).' MB');

        return self::SUCCESS;
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371;
        $toRad = static fn (float $v): float => $v * M_PI / 180;
        $dLat = $toRad($lat2 - $lat1);
        $dLon = $toRad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos($toRad($lat1)) * cos($toRad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }
}

