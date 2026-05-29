<?php

namespace App\Http\Controllers;

use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function index(Request $request, WeatherService $weather): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        if (empty(config('services.openweathermap.key'))) {
            return response()->json([
                'message' => 'El servicio meteorológico no está configurado.',
            ], 503);
        }

        $data = $weather->getWeather((float) $request->query('lat'), (float) $request->query('lng'));

        if ($data === null) {
            return response()->json(['message' => 'El proveedor meteorológico no está disponible.'], 502);
        }

        if (isset($data['error'])) {
            return response()->json(['message' => $data['error']], 503);
        }

        return response()->json(['data' => $data]);
    }
}
