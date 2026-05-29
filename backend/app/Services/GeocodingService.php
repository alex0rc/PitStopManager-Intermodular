<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    /**
     * @return array{latitude: float, longitude: float}|null
     */
    public function resolve(string $city, ?string $province = null, ?string $country = 'España'): ?array
    {
        $city = trim($city);
        if ($city === '') {
            return null;
        }

        $parts = array_filter([$city, $province, $country]);
        $query = implode(', ', $parts);

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'PitStopManager/1.0 (contact@pitstopmanager.local)',
            ])
                ->timeout(8)
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $query,
                    'format' => 'json',
                    'limit' => 1,
                ]);

            if (!$response->successful()) {
                return null;
            }

            $results = $response->json();
            if (!is_array($results) || count($results) === 0) {
                return null;
            }

            $first = $results[0];

            return [
                'latitude' => (float) $first['lat'],
                'longitude' => (float) $first['lon'],
            ];
        } catch (\Throwable $e) {
            Log::warning('Geocoding failed', ['query' => $query, 'error' => $e->getMessage()]);

            return null;
        }
    }
}
