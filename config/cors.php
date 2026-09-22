<?php

$allowedOrigins = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env(
        'CORS_ALLOWED_ORIGINS',
        'http://localhost:3000,http://localhost:3001'
    ))
)));

// Always include the explicit frontend URLs if provided.
foreach ([env('FRONTEND_URL'), env('CMS_URL')] as $extraOrigin) {
    if (is_string($extraOrigin) && $extraOrigin !== '' && ! in_array($extraOrigin, $allowedOrigins, true)) {
        $allowedOrigins[] = $extraOrigin;
    }
}

return [

    'paths' => ['api/*', 'storage/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
