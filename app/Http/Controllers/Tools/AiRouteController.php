<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Services\OpenAiRouteService;
use App\Services\OsmFeatureScannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiRouteController extends Controller
{
    protected OpenAiRouteService $openAiService;
    protected OsmFeatureScannerService $osmScannerService;

    public function __construct(OpenAiRouteService $openAiService, OsmFeatureScannerService $osmScannerService)
    {
        $this->openAiService = $openAiService;
        $this->osmScannerService = $osmScannerService;
    }

    public function generate(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'target_distance' => 'required|numeric|min:0.5|max:100',
            'shape' => 'required|string|max:200',
            'api_key' => 'nullable|string|max:255',
            'use_ai' => 'nullable|boolean',
            'scale_factor' => 'nullable|numeric|min:0.5|max:5.0',
        ]);

        $lat = (float) $request->lat;
        $lng = (float) $request->lng;
        $dist = (float) $request->target_distance;
        $shape = strtolower(trim($request->shape));
        $useAi = $request->boolean('use_ai', false);
        $userApiKey = $request->api_key;
        $scaleFactor = (float) $request->input('scale_factor', 1.0);

        // 1. Smart Map Scanner: Search for real nearby road geometry (e.g. Bundaran, Park Loop, Triangular Intersections)
        $smartOsmResult = $this->osmScannerService->findSmartShapeWaypoints($lat, $lng, $dist, $shape);
        if ($smartOsmResult && !empty($smartOsmResult['waypoints'])) {
            return response()->json([
                'success' => true,
                'mode' => 'osm_smart_feature',
                'shape_name' => $smartOsmResult['feature_name'] ?? ucfirst($shape),
                'description' => 'Terdeteksi fitur geometri jalan raya riil di sekitar lokasi',
                'waypoints' => $smartOsmResult['waypoints'],
                'relocated_center' => $smartOsmResult['center'] ?? null,
                'recommended_draw_mode' => 'osrm',
            ]);
        }

        // 2. OpenAI ChatGPT Generation (If requested by user or with API key)
        if ($useAi || !empty($userApiKey) || (!empty(env('OPENAI_API_KEY')) && !in_array($shape, ['pistol', 'heart', 'star', 'triangle', 'circle', 'number8']))) {
            try {
                $result = $this->openAiService->generateWaypoints($lat, $lng, $dist, $shape, $userApiKey);
                return response()->json([
                    'success' => true,
                    'mode' => 'openai',
                    'shape_name' => $result['shape_name'] ?? ucfirst($shape),
                    'description' => $result['description'] ?? 'Rute di-generate dengan OpenAI GPT',
                    'waypoints' => $result['waypoints'],
                ]);
            } catch (\Throwable $e) {
                Log::warning('AI Generation Fallback to Local Vector: ' . $e->getMessage());
            }
        }

        // 3. Local Perimeter-Calibrated Vector Shape Engine
        $localResult = $this->generateLocalVectorShape($lat, $lng, $dist, $shape, $scaleFactor);

        return response()->json([
            'success' => true,
            'mode' => 'local_vector',
            'shape_name' => $localResult['name'],
            'description' => $localResult['description'],
            'waypoints' => $localResult['waypoints'],
            'recommended_draw_mode' => 'osrm',
        ]);
    }

    /**
     * Generate normalized spatial vector waypoints scaled precisely to match target perimeter.
     */
    protected function generateLocalVectorShape(float $centerLat, float $centerLng, float $distKm, string $shapeKey, float $scaleMultiplier = 1.0): array
    {
        // Conversion ratio approx: 1 deg lat = 111 km, 1 deg lng = 111 * cos(lat) km
        $latKmRatio = 1 / 111.0;
        $lngKmRatio = 1 / (111.0 * max(0.2, cos(deg2rad($centerLat))));

        $normalizedPoints = [];
        $shapeName = 'Kustom';
        $description = 'Rute vektor presisi';

        switch ($shapeKey) {
            case 'pistol':
            case 'gun':
                $shapeName = 'Pistol (Gun Art)';
                $description = 'Siluet pistol presisi terkalibrasi jarak';
                $normalizedPoints = [
                    [0.0, 0.0],      // Bottom-left of grip
                    [0.15, 0.0],     // Bottom-right of grip
                    [0.25, 0.45],    // Grip top-right / backstrap
                    [0.35, 0.55],    // Beavertail / Hammer area
                    [0.20, 0.75],    // Slide rear top
                    [1.20, 0.75],    // Muzzle top-right
                    [1.20, 0.50],    // Muzzle tip front-bottom
                    [0.55, 0.50],    // Frame under barrel / Picatinny rail
                    [0.55, 0.35],    // Trigger guard front-bottom
                    [0.35, 0.35],    // Trigger guard rear-bottom
                    [0.40, 0.48],    // Trigger curve inside
                    [0.30, 0.45],    // Frame to grip junction
                    [0.0, 0.0],      // Back to grip start
                ];
                break;

            case 'heart':
            case 'hati':
            case 'love':
                $shapeName = 'Hati (Heart Shape)';
                $description = 'Siluet hati romantis simetris terkalibrasi jarak';
                $steps = 28;
                for ($i = 0; $i <= $steps; $i++) {
                    $t = ($i / $steps) * 2 * M_PI;
                    $x = 16 * pow(sin($t), 3) / 16;
                    $y = (13 * cos($t) - 5 * cos(2 * $t) - 2 * cos(3 * $t) - cos(4 * $t)) / 16;
                    $normalizedPoints[] = [$x, $y];
                }
                break;

            case 'star':
            case 'bintang':
                $shapeName = 'Bintang (Star Shape)';
                $description = 'Rute segi lima berbentuk bintang simetris';
                $points = 5;
                for ($i = 0; $i < $points * 2; $i++) {
                    $r = ($i % 2 === 0) ? 1.0 : 0.42;
                    $angle = ($i * M_PI / $points) - (M_PI / 2);
                    $normalizedPoints[] = [$r * cos($angle), $r * sin($angle)];
                }
                $normalizedPoints[] = $normalizedPoints[0];
                break;

            case 'triangle':
            case 'segitiga':
                $shapeName = 'Segitiga (Triangle Loop)';
                $description = 'Rute tiga sudut terkalibrasi jarak';
                $normalizedPoints = [
                    [0, 1.0],
                    [0.866, -0.5],
                    [-0.866, -0.5],
                    [0, 1.0]
                ];
                break;

            case 'circle':
            case 'lingkaran':
                $shapeName = 'Lingkaran Presisi (Circle Loop)';
                $description = 'Rute lingkaran halus terkalibrasi jarak';
                $steps = 24;
                for ($i = 0; $i <= $steps; $i++) {
                    $angle = ($i / $steps) * 2 * M_PI;
                    $normalizedPoints[] = [cos($angle), sin($angle)];
                }
                break;

            case 'number8':
            case 'angka8':
            case 'infinity':
                $shapeName = 'Angka 8 / Infinity';
                $description = 'Rute dua loop simetris angka 8';
                $steps = 32;
                for ($i = 0; $i <= $steps; $i++) {
                    $t = ($i / $steps) * 2 * M_PI;
                    $x = sin($t);
                    $y = sin($t) * cos($t);
                    $normalizedPoints[] = [$x, $y];
                }
                break;

            default:
                $shapeName = ucfirst($shapeKey) . ' Route';
                $description = 'Rute buatan otomatis';
                $steps = 10;
                for ($i = 0; $i <= $steps; $i++) {
                    $angle = ($i / $steps) * 2 * M_PI;
                    $jitter = 0.8 + (rand(0, 10) / 100);
                    $normalizedPoints[] = [$jitter * cos($angle), $jitter * sin($angle)];
                }
                break;
        }

        // Calculate initial unit perimeter of normalizedPoints
        $unitPerimeter = 0.0;
        $count = count($normalizedPoints);
        for ($i = 1; $i < $count; $i++) {
            $dx = $normalizedPoints[$i][0] - $normalizedPoints[$i - 1][0];
            $dy = $normalizedPoints[$i][1] - $normalizedPoints[$i - 1][1];
            $unitPerimeter += sqrt($dx * $dx + $dy * $dy);
        }

        // Calibrate scale factor so the perimeter of waypoints matches target distance (distKm)
        $effectiveMultiplier = max(0.5, min(3.0, $scaleMultiplier));
        if ($unitPerimeter > 0) {
            $scale = ($distKm / $unitPerimeter) * $effectiveMultiplier;
        } else {
            $scale = ($distKm / (2 * M_PI)) * $effectiveMultiplier;
        }

        // Project normalized relative coordinates to lat/lng waypoints
        $waypoints = [];
        foreach ($normalizedPoints as $pt) {
            $relX = $pt[0] * $scale;
            $relY = $pt[1] * $scale;

            $lat = $centerLat + ($relY * $latKmRatio);
            $lng = $centerLng + ($relX * $lngKmRatio);

            $waypoints[] = [
                'lat' => round($lat, 6),
                'lng' => round($lng, 6),
            ];
        }

        return [
            'name' => $shapeName,
            'description' => $description,
            'waypoints' => $waypoints,
        ];
    }
}
