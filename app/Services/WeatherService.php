<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class WeatherService
{
    private string $baseUrl;
    private ?string $apiKey;
    private int $timeout;
    private int $retries;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.weather.base_url', 'https://weather.googleapis.com/v1'), '/');
        $this->apiKey = config('services.weather.api_key');
        $this->timeout = (int) config('services.weather.timeout', 10);
        $this->retries = (int) config('services.weather.retries', 1);
    }

    public function resolveMunicipality(string $municipality): ?string
    {
        $lookupKey = $this->normalizeMunicipalityKey($municipality);

        foreach (array_keys((array) config('weather.municipalities', [])) as $configuredMunicipality) {
            if ($this->normalizeMunicipalityKey($configuredMunicipality) === $lookupKey) {
                return $configuredMunicipality;
            }
        }

        return null;
    }

    public function getMapWeather(string $municipality, int $hours, int $days): array
    {
        $coordinates = $this->getCoordinates($municipality);

        if ($coordinates === null) {
            return [
                'municipality' => $municipality,
                'coordinates' => null,
                'current' => null,
                'hourly' => [
                    'hours' => $hours,
                    'items' => [],
                ],
                'daily' => [
                    'days' => $days,
                    'items' => [],
                ],
                'errors' => [
                    'global' => 'Municipality is not mapped to weather coordinates.',
                ],
            ];
        }

        $current = $this->fetchSegmentWithCache(
            'current',
            $municipality,
            (int) config('weather.cache.current_ttl', 1800),
            fn () => $this->fetchCurrent($coordinates)
        );

        $hourly = $this->fetchSegmentWithCache(
            'hourly',
            $municipality . ':' . $hours,
            (int) config('weather.cache.hourly_ttl', 1800),
            fn () => $this->fetchHourly($coordinates, $hours)
        );

        $daily = $this->fetchSegmentWithCache(
            'daily',
            $municipality . ':' . $days,
            (int) config('weather.cache.daily_ttl', 21600),
            fn () => $this->fetchDaily($coordinates, $days)
        );

        return [
            'municipality' => $municipality,
            'coordinates' => $coordinates,
            'current' => $current['data'],
            'hourly' => [
                'hours' => $hours,
                'items' => $hourly['data'] ?? [],
            ],
            'daily' => [
                'days' => $days,
                'items' => $daily['data'] ?? [],
            ],
            'errors' => [
                'current' => $current['error'],
                'hourly' => $hourly['error'],
                'daily' => $daily['error'],
            ],
            'metadata' => [
                'source' => [
                    'current' => $current['source'],
                    'hourly' => $hourly['source'],
                    'daily' => $daily['source'],
                ],
                'stale' => [
                    'current' => $current['is_stale'],
                    'hourly' => $hourly['is_stale'],
                    'daily' => $daily['is_stale'],
                ],
                'fetched_at' => now()->toIso8601String(),
            ],
        ];
    }

    private function getCoordinates(string $municipality): ?array
    {
        $allMunicipalities = (array) config('weather.municipalities', []);

        return $allMunicipalities[$municipality] ?? null;
    }

    private function fetchSegmentWithCache(string $segment, string $cacheSuffix, int $ttl, callable $fetcher): array
    {
        $normalizedSuffix = $this->normalizeMunicipalityKey($cacheSuffix);
        $cacheKey = "weather:map:{$segment}:{$normalizedSuffix}";
        $staleKey = "{$cacheKey}:stale";

        if (Cache::has($cacheKey)) {
            return [
                'data' => Cache::get($cacheKey),
                'error' => null,
                'source' => 'cache',
                'is_stale' => false,
            ];
        }

        try {
            $freshData = $fetcher();

            Cache::put($cacheKey, $freshData, $ttl);
            Cache::put($staleKey, $freshData, (int) config('weather.cache.stale_ttl', 86400));

            return [
                'data' => $freshData,
                'error' => null,
                'source' => 'live',
                'is_stale' => false,
            ];
        } catch (Throwable $exception) {
            Log::warning('Weather segment fetch failed.', [
                'segment' => $segment,
                'cache_suffix' => $cacheSuffix,
                'error' => $exception->getMessage(),
            ]);

            if (Cache::has($staleKey)) {
                return [
                    'data' => Cache::get($staleKey),
                    'error' => 'Serving stale weather data while provider is unavailable.',
                    'source' => 'stale-cache',
                    'is_stale' => true,
                ];
            }

            return [
                'data' => $segment === 'current' ? null : [],
                'error' => $exception->getMessage(),
                'source' => 'error',
                'is_stale' => false,
            ];
        }
    }

    private function fetchCurrent(array $coordinates): array
    {
        $payload = $this->request('currentConditions:lookup', [
            'location.latitude' => $coordinates['latitude'],
            'location.longitude' => $coordinates['longitude'],
        ]);

        return [
            'timestamp' => $this->firstValue($payload, ['currentTime', 'dateTime', 'interval.startTime']),
            'condition_text' => $this->firstValue($payload, [
                'weatherCondition.description.text',
                'condition.description.text',
                'weather.condition.text',
            ]),
            'icon' => $this->firstValue($payload, [
                'weatherCondition.iconBaseUri',
                'condition.iconBaseUri',
                'weather.iconUrl',
            ]),
            'temperature_c' => $this->normalizeTemperatureFromNode(
                $this->firstValue($payload, ['temperature', 'temperatureInfo', 'currentTemperature'])
            ),
            'feels_like_c' => $this->normalizeTemperatureFromNode(
                $this->firstValue($payload, ['feelsLikeTemperature', 'apparentTemperature'])
            ),
            'humidity_percent' => $this->normalizePercent(
                $this->firstValue($payload, ['relativeHumidity', 'humidity.percent', 'humidity'])
            ),
            'precipitation_probability_percent' => $this->normalizePercent(
                $this->firstValue($payload, [
                    'precipitation.probability.percent',
                    'precipitationProbability.percent',
                    'precipitationProbability',
                ])
            ),
            'wind_speed_kph' => $this->normalizeWindSpeedFromNode(
                $this->firstValue($payload, ['wind', 'windSpeed'])
            ),
            'wind_direction' => $this->firstValue($payload, [
                'wind.direction.cardinal',
                'windDirection.cardinal',
                'wind.direction',
                'windDirection',
            ]),
        ];
    }

    private function fetchHourly(array $coordinates, int $hours): array
    {
        $payload = $this->request('forecast/hours:lookup', [
            'location.latitude' => $coordinates['latitude'],
            'location.longitude' => $coordinates['longitude'],
            'hours' => $hours,
        ]);

        $entries = $this->firstValue($payload, ['forecastHours', 'hourlyForecasts', 'hours'], []);

        if (!is_array($entries)) {
            return [];
        }

        $entries = array_slice($entries, 0, $hours);

        return array_values(array_map(function ($entry) {
            return [
                'timestamp' => $this->firstValue((array) $entry, ['interval.startTime', 'startTime', 'time']),
                'condition_text' => $this->firstValue((array) $entry, [
                    'weatherCondition.description.text',
                    'condition.description.text',
                    'weather.condition.text',
                ]),
                'icon' => $this->firstValue((array) $entry, [
                    'weatherCondition.iconBaseUri',
                    'condition.iconBaseUri',
                    'weather.iconUrl',
                ]),
                'temperature_c' => $this->normalizeTemperatureFromNode(
                    $this->firstValue((array) $entry, ['temperature', 'airTemperature'])
                ),
                'precipitation_probability_percent' => $this->normalizePercent(
                    $this->firstValue((array) $entry, [
                        'precipitation.probability.percent',
                        'precipitationProbability.percent',
                        'precipitationProbability',
                    ])
                ),
                'humidity_percent' => $this->normalizePercent(
                    $this->firstValue((array) $entry, ['relativeHumidity', 'humidity.percent', 'humidity'])
                ),
                'wind_speed_kph' => $this->normalizeWindSpeedFromNode(
                    $this->firstValue((array) $entry, ['wind', 'windSpeed'])
                ),
            ];
        }, $entries));
    }

    private function fetchDaily(array $coordinates, int $days): array
    {
        $payload = $this->request('forecast/days:lookup', [
            'location.latitude' => $coordinates['latitude'],
            'location.longitude' => $coordinates['longitude'],
            'days' => $days,
        ]);

        $entries = $this->firstValue($payload, ['forecastDays', 'dailyForecasts', 'days'], []);

        if (!is_array($entries)) {
            return [];
        }

        $entries = array_slice($entries, 0, $days);

        return array_values(array_map(function ($entry) {
            $entry = (array) $entry;

            return [
                'date' => $this->formatDateNode($this->firstValue($entry, ['displayDate', 'interval.startTime', 'date'])),
                'condition_text' => $this->firstValue($entry, [
                    'daytimeForecast.weatherCondition.description.text',
                    'weatherCondition.description.text',
                    'condition.description.text',
                ]),
                'icon' => $this->firstValue($entry, [
                    'daytimeForecast.weatherCondition.iconBaseUri',
                    'weatherCondition.iconBaseUri',
                    'condition.iconBaseUri',
                ]),
                'temp_max_c' => $this->normalizeTemperatureFromNode(
                    $this->firstValue($entry, ['maxTemperature', 'temperatureMax'])
                ),
                'temp_min_c' => $this->normalizeTemperatureFromNode(
                    $this->firstValue($entry, ['minTemperature', 'temperatureMin'])
                ),
                'precipitation_probability_percent' => $this->normalizePercent(
                    $this->firstValue($entry, [
                        'daytimeForecast.precipitation.probability.percent',
                        'precipitation.probability.percent',
                        'precipitationProbability.percent',
                        'precipitationProbability',
                    ])
                ),
            ];
        }, $entries));
    }

    private function request(string $endpoint, array $query): array
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('Weather API key is not configured. Set WEATHER_API_KEY in Railway variables.');
        }

        $response = Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->timeout($this->timeout)
            ->retry($this->retries, 300)
            ->get($endpoint, array_merge($query, ['key' => $this->apiKey]));

        if (!$response->successful()) {
            $status = $response->status();
            throw new RuntimeException("Weather provider request failed with status {$status}.");
        }

        return $response->json() ?? [];
    }

    private function normalizeMunicipalityKey(string $municipality): string
    {
        return strtoupper(str_replace(' ', '', trim($municipality)));
    }

    private function firstValue(array $payload, array $paths, mixed $default = null): mixed
    {
        foreach ($paths as $path) {
            $value = data_get($payload, $path);
            if ($value !== null) {
                return $value;
            }
        }

        return $default;
    }

    private function normalizeTemperatureFromNode(mixed $temperatureNode): ?float
    {
        if ($temperatureNode === null) {
            return null;
        }

        if (is_numeric($temperatureNode)) {
            return round((float) $temperatureNode, 1);
        }

        if (!is_array($temperatureNode)) {
            return null;
        }

        $value = data_get($temperatureNode, 'degrees', data_get($temperatureNode, 'value'));
        if (!is_numeric($value)) {
            return null;
        }

        $unit = data_get($temperatureNode, 'unit');

        return round($this->toCelsius((float) $value, $unit), 1);
    }

    private function normalizeWindSpeedFromNode(mixed $windNode): ?float
    {
        if ($windNode === null) {
            return null;
        }

        if (is_numeric($windNode)) {
            return round((float) $windNode, 1);
        }

        if (!is_array($windNode)) {
            return null;
        }

        $value = data_get($windNode, 'speed.value', data_get($windNode, 'value'));
        if (!is_numeric($value)) {
            return null;
        }

        $unit = data_get($windNode, 'speed.unit', data_get($windNode, 'unit'));

        return round($this->toKph((float) $value, $unit), 1);
    }

    private function normalizePercent(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        $number = (float) $value;

        if ($number <= 1) {
            $number *= 100;
        }

        return round($number, 1);
    }

    private function formatDateNode(mixed $dateNode): ?string
    {
        if ($dateNode === null) {
            return null;
        }

        if (is_string($dateNode)) {
            return str_contains($dateNode, 'T') ? substr($dateNode, 0, 10) : $dateNode;
        }

        if (!is_array($dateNode)) {
            return null;
        }

        $year = data_get($dateNode, 'year');
        $month = data_get($dateNode, 'month');
        $day = data_get($dateNode, 'day');

        if (!is_numeric($year) || !is_numeric($month) || !is_numeric($day)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', (int) $year, (int) $month, (int) $day);
    }

    private function toCelsius(float $value, ?string $unit): float
    {
        $normalizedUnit = strtoupper((string) $unit);

        if (str_contains($normalizedUnit, 'FAHREN')) {
            return ($value - 32) * 5 / 9;
        }

        return $value;
    }

    private function toKph(float $value, ?string $unit): float
    {
        $normalizedUnit = strtoupper((string) $unit);

        if (str_contains($normalizedUnit, 'MILES')) {
            return $value * 1.60934;
        }

        if (str_contains($normalizedUnit, 'METERS_PER_SECOND')) {
            return $value * 3.6;
        }

        return $value;
    }
}
