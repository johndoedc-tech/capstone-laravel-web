<?php

return [
    'defaults' => [
        'hours' => 24,
        'days' => 7,
    ],

    'limits' => [
        'max_hours' => 72,
        'max_days' => 14,
    ],

    'cache' => [
        'current_ttl' => (int) env('WEATHER_CACHE_CURRENT_TTL', 1800),
        'hourly_ttl' => (int) env('WEATHER_CACHE_HOURLY_TTL', 1800),
        'daily_ttl' => (int) env('WEATHER_CACHE_DAILY_TTL', 21600),
        'stale_ttl' => (int) env('WEATHER_CACHE_STALE_TTL', 86400),
    ],

    'municipalities' => [
        'ATOK' => [
            'latitude' => 16.6274093,
            'longitude' => 120.7675527,
        ],
        'BAKUN' => [
            'latitude' => 16.8300411,
            'longitude' => 120.6830301,
        ],
        'BOKOD' => [
            'latitude' => 16.4908605,
            'longitude' => 120.8302587,
        ],
        'BUGUIAS' => [
            'latitude' => 16.7192014,
            'longitude' => 120.826902,
        ],
        'ITOGON' => [
            'latitude' => 16.3657698,
            'longitude' => 120.633172,
        ],
        'KABAYAN' => [
            'latitude' => 16.6239201,
            'longitude' => 120.8381884,
        ],
        'KAPANGAN' => [
            'latitude' => 16.5761774,
            'longitude' => 120.6030069,
        ],
        'KIBUNGAN' => [
            'latitude' => 16.6937271,
            'longitude' => 120.6533943,
        ],
        'LA TRINIDAD' => [
            'latitude' => 16.4586825,
            'longitude' => 120.5812456,
        ],
        'MANKAYAN' => [
            'latitude' => 16.8572602,
            'longitude' => 120.7933631,
        ],
        'SABLAN' => [
            'latitude' => 16.4966909,
            'longitude' => 120.4875959,
        ],
        'TUBA' => [
            'latitude' => 16.3926636,
            'longitude' => 120.5612911,
        ],
        'TUBLAY' => [
            'latitude' => 16.5145931,
            'longitude' => 120.6322972,
        ],
    ],
];
