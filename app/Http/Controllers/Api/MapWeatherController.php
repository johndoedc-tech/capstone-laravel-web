<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapWeatherController extends Controller
{
    /**
     * GET /api/map/weather/{municipality}?hours=24&days=7
     */
    public function show(Request $request, string $municipality, WeatherService $weatherService): JsonResponse
    {
        $validated = $request->validate([
            'hours' => ['nullable', 'integer', 'min:1'],
            'days' => ['nullable', 'integer', 'min:1'],
        ]);

        $defaultHours = (int) config('weather.defaults.hours', 24);
        $defaultDays = (int) config('weather.defaults.days', 7);
        $maxHours = (int) config('weather.limits.max_hours', 72);
        $maxDays = (int) config('weather.limits.max_days', 14);

        $hours = min($maxHours, (int) ($validated['hours'] ?? $defaultHours));
        $days = min($maxDays, (int) ($validated['days'] ?? $defaultDays));

        $resolvedMunicipality = $weatherService->resolveMunicipality($municipality);

        if ($resolvedMunicipality === null) {
            return response()->json([
                'success' => false,
                'message' => 'Unknown municipality for weather lookup.',
                'municipality' => $municipality,
                'supported_municipalities' => array_keys((array) config('weather.municipalities', [])),
            ], 404);
        }

        $payload = $weatherService->getMapWeather($resolvedMunicipality, $hours, $days);
        $segmentErrors = array_filter((array) ($payload['errors'] ?? []));

        return response()->json([
            'success' => true,
            'municipality' => $resolvedMunicipality,
            'request' => [
                'hours' => $hours,
                'days' => $days,
            ],
            'weather' => $payload,
            'has_errors' => !empty($segmentErrors),
        ]);
    }
}
