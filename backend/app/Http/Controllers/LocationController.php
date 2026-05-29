<?php

namespace App\Http\Controllers;

use App\Services\GeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function countries(): JsonResponse
    {
        return response()->json([
            'data' => config('spain_locations.countries', ['España']),
        ]);
    }

    public function provinces(Request $request): JsonResponse
    {
        $country = $request->input('country', 'España');
        $provinces = config("spain_locations.provinces.{$country}", []);

        return response()->json(['data' => $provinces]);
    }

    public function cities(Request $request): JsonResponse
    {
        $province = $request->input('province', '');
        $cities = config("spain_locations.cities.{$province}", []);

        return response()->json(['data' => $cities]);
    }

    public function geocode(Request $request, GeocodingService $geocoding): JsonResponse
    {
        $data = $request->validate([
            'city' => ['required', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
        ]);

        $coords = $geocoding->resolve(
            $data['city'],
            $data['province'] ?? null,
            $data['country'] ?? 'España',
        );

        if (!$coords) {
            return response()->json([
                'message' => 'No se encontraron coordenadas para esa ubicación.',
            ], 422);
        }

        return response()->json(['data' => $coords]);
    }
}
