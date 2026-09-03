<?php

namespace App\Services;

use App\Models\City;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RajaOngkirService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = (string) config('services.rajaongkir.key', 'c7d5ff5a5a151bba9b434e9deae19edf');
        $this->baseUrl = rtrim(config('services.rajaongkir.base_url', 'https://api.rajaongkir.com/starter/'), '/') . '/';
    }

    /**
     * Get all cities from RajaOngkir (cached 7 days)
     */
    public function getCities(): array
    {
        return Cache::remember('rajaongkir_all_cities_v2', 86400 * 7, function () {
            try {
                $response = Http::withHeaders([
                    'key' => $this->apiKey,
                ])->timeout(8)->get($this->baseUrl . 'city');

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['rajaongkir']['results'] ?? [];
                }

                Log::warning('RajaOngkir getCities response non-200: ' . $response->body());
            } catch (\Throwable $e) {
                Log::error('RajaOngkir getCities failed: ' . $e->getMessage());
            }

            // Fallback from local DB if RajaOngkir fails
            return $this->getLocalCitiesFallback();
        });
    }

    /**
     * Fast autocomplete search for cities
     */
    public function searchCities(string $keyword): array
    {
        $keyword = trim($keyword);
        if (mb_strlen($keyword) < 1) {
            return [];
        }

        $allCities = $this->getCities();
        $q = strtolower($keyword);

        $matched = [];
        foreach ($allCities as $city) {
            $cityName = strtolower($city['city_name'] ?? '');
            $type = strtolower($city['type'] ?? '');
            $province = strtolower($city['province'] ?? '');
            $combined = "{$type} {$cityName} {$province}";

            if (str_contains($combined, $q) || str_contains($cityName, $q)) {
                $matched[] = [
                    'id' => (int) ($city['city_id'] ?? 0),
                    'city_id' => (int) ($city['city_id'] ?? 0),
                    'city_name' => $city['city_name'] ?? '',
                    'type' => $city['type'] ?? '',
                    'province' => $city['province'] ?? '',
                    'postal_code' => $city['postal_code'] ?? '',
                    'display' => ($city['type'] ?? 'Kota') . ' ' . ($city['city_name'] ?? '') . ', ' . ($city['province'] ?? ''),
                ];
            }

            if (count($matched) >= 15) {
                break;
            }
        }

        return $matched;
    }

    /**
     * Find city ID by city name
     */
    public function findCityIdByName(?string $cityName): int
    {
        if (empty($cityName)) {
            return 152; // Default: Jakarta Selatan
        }

        $allCities = $this->getCities();
        $q = strtolower(trim($cityName));

        // 1. Detect preference for Kota vs Kabupaten
        $wantsKota = (bool) preg_match('/\b(kota|kotamadya)\b/i', $q);
        $wantsKabupaten = (bool) preg_match('/\b(kabupaten|kab|kab\.)\b/i', $q);

        // Strip prefix and suffix
        $cleanQ = preg_replace('/^(kota|kabupaten|kab\.)\s+/i', '', $q);
        $cleanQ = preg_replace('/\s+(kota|kabupaten|kab\.)$/i', '', $cleanQ);
        $cleanQ = trim($cleanQ);

        // A. Exact match considering type (e.g. "Malang Kota" -> type: "Kota", name: "Malang")
        if ($wantsKota || $wantsKabupaten) {
            $targetType = $wantsKota ? 'kota' : 'kabupaten';
            foreach ($allCities as $city) {
                $name = strtolower($city['city_name'] ?? '');
                $type = strtolower($city['type'] ?? '');
                if ($name === $cleanQ && $type === $targetType) {
                    return (int) $city['city_id'];
                }
            }
        }

        // B. Exact match on city_name only
        foreach ($allCities as $city) {
            $name = strtolower($city['city_name'] ?? '');
            if ($name === $cleanQ || $name === $q) {
                return (int) $city['city_id'];
            }
        }

        // C. Partial match considering type
        if ($wantsKota || $wantsKabupaten) {
            $targetType = $wantsKota ? 'kota' : 'kabupaten';
            foreach ($allCities as $city) {
                $name = strtolower($city['city_name'] ?? '');
                $type = strtolower($city['type'] ?? '');
                if ($type === $targetType && (str_contains($name, $cleanQ) || str_contains($cleanQ, $name))) {
                    return (int) $city['city_id'];
                }
            }
        }

        // D. Any partial match
        foreach ($allCities as $city) {
            $name = strtolower($city['city_name'] ?? '');
            if (str_contains($name, $cleanQ) || str_contains($cleanQ, $name)) {
                return (int) $city['city_id'];
            }
        }

        return 152; // Default fallback to Jakarta Selatan
    }

    /**
     * Check if two city IDs belong to the same local metropolitan / twin-city zone
     */
    public function areTwinCities(int $originId, int $destId): bool
    {
        if ($originId === $destId) {
            return true;
        }

        // Twin city pairs (Kota & Kabupaten in the same delivery area)
        $twins = [
            [255, 256], // Malang (Kabupaten & Kota)
            [22, 23],   // Bandung (Kota & Kabupaten)
            [78, 79],   // Bogor (Kota & Kabupaten)
            [54, 55],   // Bekasi (Kota & Kabupaten)
            [455, 456], // Tangerang (Kabupaten & Kota)
            [457, 458], // Tangerang Selatan & Tangerang
            [418, 420], // Semarang (Kabupaten & Kota)
            [444, 445], // Surakarta / Solo
            [108, 109], // Cirebon (Kota & Kabupaten)
            [398, 399], // Probolinggo (Kabupaten & Kota)
            [342, 343], // Pasuruan (Kabupaten & Kota)
            [246, 247], // Madiun (Kabupaten & Kota)
            [177, 178], // Kediri (Kabupaten & Kota)
            [430, 431], // Sukabumi (Kabupaten & Kota)
            [468, 469], // Tasikmalaya (Kabupaten & Kota)
            [427, 428], // Serang (Kabupaten & Kota)
            [151, 152, 153, 154, 155], // DKI Jakarta zones
            [501, 419, 39], // Yogyakarta, Sleman, Bantul
        ];

        foreach ($twins as $group) {
            if (in_array($originId, $group, true) && in_array($destId, $group, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate Shipping Cost from Origin to Destination
     *
     * @param int $originCityId
     * @param int $destinationCityId
     * @param int $weightInGrams
     * @param array $couriers (['jne', 'pos', 'tiki'])
     * @return array
     */
    public function calculateShippingCost(int $originCityId, int $destinationCityId, int $weightInGrams = 1000, array $couriers = ['jne', 'pos', 'tiki']): array
    {
        $weightInGrams = max(100, $weightInGrams);
        $results = [];

        foreach ($couriers as $courier) {
            $cacheKey = "ro_cost_v2_{$originCityId}_{$destinationCityId}_{$weightInGrams}_{$courier}";

            $courierServices = Cache::remember($cacheKey, 3600, function () use ($originCityId, $destinationCityId, $weightInGrams, $courier) {
                return $this->fetchCourierCost($originCityId, $destinationCityId, $weightInGrams, $courier);
            });

            if (!empty($courierServices)) {
                $results = array_merge($results, $courierServices);
            }
        }

        // If no results from API (e.g. timeout or same origin/dest), provide reliable fallback rates
        if (empty($results)) {
            $results = $this->getFallbackShippingOptions($originCityId, $destinationCityId);
        }

        // Add Instant / Pickup Option (COD)
        $results[] = [
            'courier_code' => 'pickup',
            'courier_name' => 'Ambil Sendiri / COD',
            'service' => 'PICKUP',
            'description' => 'Ambil sendiri di lokasi seller / Titip Jual Hub',
            'cost' => 0,
            'etd' => 'Langsung',
            'formatted_cost' => 'Gratis (Rp 0)',
        ];

        return $results;
    }

    /**
     * Query RajaOngkir cost endpoint
     */
    protected function fetchCourierCost(int $origin, int $destination, int $weight, string $courier): array
    {
        try {
            $response = Http::asForm()->withHeaders([
                'key' => $this->apiKey,
            ])->timeout(6)->post($this->baseUrl . 'cost', [
                'origin' => $origin,
                'destination' => $destination,
                'weight' => $weight,
                'courier' => strtolower($courier),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $courierData = $data['rajaongkir']['results'][0] ?? null;

                if ($courierData && !empty($courierData['costs'])) {
                    $services = [];
                    foreach ($courierData['costs'] as $c) {
                        $costVal = $c['cost'][0]['value'] ?? 0;
                        $etd = $c['cost'][0]['etd'] ?? '-';
                        if (!empty($etd) && !str_contains(strtolower($etd), 'hari') && is_numeric(trim($etd))) {
                            $etd .= ' hari';
                        }

                        $services[] = [
                            'courier_code' => strtolower($courierData['code']),
                            'courier_name' => strtoupper($courierData['name'] ?? $courierData['code']),
                            'service' => $c['service'],
                            'description' => $c['description'] ?: $c['service'],
                            'cost' => (int) $costVal,
                            'etd' => $etd ?: '2-3 hari',
                            'formatted_cost' => 'Rp ' . number_format($costVal, 0, ',', '.'),
                        ];
                    }
                    return $services;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("RajaOngkir fetchCourierCost [{$courier}] error: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Fallback standard shipping rates when API is offline
     */
    protected function getFallbackShippingOptions(int $origin, int $destination): array
    {
        $isSameCity = ($origin === $destination) || $this->areTwinCities($origin, $destination);

        return [
            [
                'courier_code' => 'jne',
                'courier_name' => 'JNE',
                'service' => 'REG',
                'description' => 'Layanan Reguler',
                'cost' => $isSameCity ? 10000 : 22000,
                'etd' => $isSameCity ? '1-2 hari' : '2-3 hari',
                'formatted_cost' => 'Rp ' . number_format($isSameCity ? 10000 : 22000, 0, ',', '.'),
            ],
            [
                'courier_code' => 'jne',
                'courier_name' => 'JNE',
                'service' => 'YES',
                'description' => 'Yakin Esok Sampai',
                'cost' => $isSameCity ? 18000 : 38000,
                'etd' => '1 hari',
                'formatted_cost' => 'Rp ' . number_format($isSameCity ? 18000 : 38000, 0, ',', '.'),
            ],
            [
                'courier_code' => 'pos',
                'courier_name' => 'POS INDONESIA',
                'service' => 'KILAT',
                'description' => 'Pos Kilat Khusus',
                'cost' => $isSameCity ? 9000 : 20000,
                'etd' => $isSameCity ? '1-2 hari' : '2-4 hari',
                'formatted_cost' => 'Rp ' . number_format($isSameCity ? 9000 : 20000, 0, ',', '.'),
            ],
            [
                'courier_code' => 'tiki',
                'courier_name' => 'TIKI',
                'service' => 'REG',
                'description' => 'Regular Service',
                'cost' => $isSameCity ? 11000 : 23000,
                'etd' => $isSameCity ? '1-2 hari' : '2-3 hari',
                'formatted_cost' => 'Rp ' . number_format($isSameCity ? 11000 : 23000, 0, ',', '.'),
            ],
        ];
    }

    /**
     * Fallback database cities
     */
    protected function getLocalCitiesFallback(): array
    {
        $jsonPath = database_path('data/rajaongkir_cities.json');
        if (file_exists($jsonPath)) {
            $json = file_get_contents($jsonPath);
            $cities = json_decode($json, true);
            if (!empty($cities) && is_array($cities)) {
                return $cities;
            }
        }

        try {
            if (class_exists(City::class)) {
                return City::with('province')
                    ->get()
                    ->map(function ($c) {
                        return [
                            'city_id' => $c->id,
                            'city_name' => $c->name,
                            'type' => 'Kota',
                            'province' => optional($c->province)->name ?? 'Indonesia',
                            'postal_code' => '',
                        ];
                    })
                    ->toArray();
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        return [];
    }
}
