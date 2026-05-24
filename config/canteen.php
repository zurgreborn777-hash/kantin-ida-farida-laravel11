<?php

return [
    'name' => env('CANTEEN_NAME', 'Kantin Ibu Ida'),
    'latitude' => (float) env('CANTEEN_LATITUDE', -6.168417),
    'longitude' => (float) env('CANTEEN_LONGITUDE', 106.834167),
    'max_delivery_km' => (float) env('CANTEEN_MAX_DELIVERY_KM', 2),
];
