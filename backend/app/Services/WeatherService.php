<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    private string $apiKey;

    private string $baseUrl = 'https://api.openweathermap.org/data/2.5/weather';

    public function __construct()
    {
        $this->apiKey = (string) config('services.openweathermap.key', '');
    }

    public function getWeather(float $lat, float $lng): ?array
    {
        if (empty($this->apiKey)) {
            return ['error' => 'API key not configured'];
        }

        $cacheKey = sprintf('weather_%.4f_%.4f', $lat, $lng);

        $cached = Cache::get($cacheKey);
        if (is_array($cached) && ! isset($cached['error'])) {
            return $cached;
        }

        $response = Http::timeout(12)->get($this->baseUrl, [
            'lat'   => $lat,
            'lon'   => $lng,
            'appid' => $this->apiKey,
            'units' => 'metric',
            'lang'  => 'es',
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $payload = [
                'temperature' => $data['main']['temp'] ?? null,
                'feels_like'  => $data['main']['feels_like'] ?? null,
                'humidity'    => $data['main']['humidity'] ?? null,
                'description' => $data['weather'][0]['description'] ?? null,
                'icon'        => $data['weather'][0]['icon'] ?? null,
                'wind_speed'  => isset($data['wind']['speed']) ? round((float) $data['wind']['speed'] * 3.6, 1) : null,
                'clouds'      => $data['clouds']['all'] ?? null,
                'city'        => $data['name'] ?? null,
            ];

            Cache::put($cacheKey, $payload, 1800);

            return $payload;
        }

        $message = $response->json('message') ?? 'Weather provider error';
        Log::warning('OpenWeather API request failed', [
            'status'  => $response->status(),
            'message' => $message,
            'lat'     => $lat,
            'lng'     => $lng,
        ]);

        return ['error' => $message];
    }
}
