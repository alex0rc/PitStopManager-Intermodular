<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Services\GeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function geocode(Request $request, GeocodingService $geocoding): JsonResponse
    {
        $data = $request->validate([
            'city'     => ['required', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'country'  => ['nullable', 'string', 'max:255'],
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
