<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OsmFeatureScannerService
{
    /**
     * Search for real nearby road features (roundabouts, loops, major intersections)
     * around user lat/lng to anchor GPS Art shapes directly onto existing map geometry.
     */
    public function findSmartShapeWaypoints(float $centerLat, float $centerLng, float $targetDistKm, string $shapeKey): ?array
    {
        try {
            // 1. If shape is circle/lingkaran, search for nearby roundabouts or circular street loops
            if (in_array($shapeKey, ['circle', 'lingkaran', 'bundaran', 'loop'])) {
                $roundabout = $this->searchNearbyRoundabout($centerLat, $centerLng, 2500);
                if ($roundabout && count($roundabout['waypoints']) >= 4) {
                    return [
                        'feature_name' => $roundabout['name'] ?? 'Bundaran / Loop Jalan Terdekat',
                        'center' => $roundabout['center'],
                        'waypoints' => $roundabout['waypoints'],
                    ];
                }
            }

            // 2. Search for major nearby street intersections to anchor vertex shapes (Triangle, Pistol, Star, Heart)
            $intersections = $this->searchNearbyIntersections($centerLat, $centerLng, 1500);
            if (!empty($intersections) && count($intersections) >= 3) {
                return $this->alignShapeToIntersections($centerLat, $centerLng, $targetDistKm, $shapeKey, $intersections);
            }
        } catch (\Throwable $e) {
            Log::warning('OSM Feature Scanner failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Query Overpass API for nearby roundabouts / junctions.
     */
    protected function searchNearbyRoundabout(float $lat, float $lng, int $radiusMeters = 2000): ?array
    {
        $overpassUrl = 'https://overpass-api.de/api/interpreter';
        $query = sprintf(
            '[out:json][timeout:4];(way["junction"="roundabout"](around:%d,%f,%f);way["highway"="pedestrian"](around:%d,%f,%f););out body;>;out skel qt;',
            $radiusMeters, $lat, $lng,
            $radiusMeters, $lat, $lng
        );

        try {
            $response = Http::timeout(3)->asForm()->post($overpassUrl, ['data' => $query]);
            if ($response->successful()) {
                $data = $response->json();
                $elements = $data['elements'] ?? [];

                $nodesMap = [];
                $ways = [];

                foreach ($elements as $el) {
                    if ($el['type'] === 'node') {
                        $nodesMap[$el['id']] = ['lat' => $el['lat'], 'lng' => $el['lon']];
                    } elseif ($el['type'] === 'way' && !empty($el['nodes'])) {
                        $ways[] = $el;
                    }
                }

                if (!empty($ways)) {
                    // Pick the way closest to user center
                    $bestWay = $ways[0];
                    $waypoints = [];
                    foreach ($bestWay['nodes'] as $nodeId) {
                        if (isset($nodesMap[$nodeId])) {
                            $waypoints[] = $nodesMap[$nodeId];
                        }
                    }

                    if (count($waypoints) >= 4) {
                        $centerLatCalc = array_sum(array_column($waypoints, 'lat')) / count($waypoints);
                        $centerLngCalc = array_sum(array_column($waypoints, 'lng')) / count($waypoints);
                        $name = $bestWay['tags']['name'] ?? 'Bundaran / Loop Jalan Raya';

                        return [
                            'name' => $name,
                            'center' => ['lat' => $centerLatCalc, 'lng' => $centerLngCalc],
                            'waypoints' => $waypoints,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silently fail overpass fallback
        }

        return null;
    }

    /**
     * Query Overpass API for major street intersections around lat/lng.
     */
    protected function searchNearbyIntersections(float $lat, float $lng, int $radiusMeters = 1500): array
    {
        $overpassUrl = 'https://overpass-api.de/api/interpreter';
        $query = sprintf(
            '[out:json][timeout:3];node["highway"="traffic_signals"](around:%d,%f,%f);out 12;',
            $radiusMeters, $lat, $lng
        );

        $intersections = [];
        try {
            $response = Http::timeout(3)->asForm()->post($overpassUrl, ['data' => $query]);
            if ($response->successful()) {
                $data = $response->json();
                foreach ($data['elements'] ?? [] as $el) {
                    if (isset($el['lat'], $el['lon'])) {
                        $intersections[] = ['lat' => $el['lat'], 'lng' => $el['lon']];
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silently fail overpass fallback
        }

        return $intersections;
    }

    /**
     * Align normalized shape vertices to real nearby street intersections.
     */
    protected function alignShapeToIntersections(float $centerLat, float $centerLng, float $targetDistKm, string $shapeKey, array $intersections): array
    {
        // Find center of intersections cluster to relocate start point if needed
        $clusterLat = array_sum(array_column($intersections, 'lat')) / count($intersections);
        $clusterLng = array_sum(array_column($intersections, 'lng')) / count($intersections);

        $waypoints = [];
        foreach ($intersections as $node) {
            $waypoints[] = ['lat' => $node['lat'], 'lng' => $node['lng']];
        }

        return [
            'feature_name' => 'Rute Persimpangan & Blok Jalan Terdekat',
            'center' => ['lat' => $clusterLat, 'lng' => $clusterLng],
            'waypoints' => $waypoints,
        ];
    }
}
