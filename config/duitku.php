<?php

return [
    'merchant_code' => env('DUITKU_MERCHANT_CODE'),
    'api_key' => env('DUITKU_API_KEY'),
    'env' => env('DUITKU_ENV', 'sandbox'),
    'callback_url' => env('DUITKU_CALLBACK_URL'),
    'return_url' => env('DUITKU_RETURN_URL'),
    'sandbox_endpoint' => 'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry',
    'production_endpoint' => 'https://passport.duitku.com/webapi/api/merchant/v2/inquiry',
];
