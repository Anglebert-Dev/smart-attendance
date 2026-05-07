<?php

return [
    'paths'                    => ['api/*'],
    'allowed_methods'          => ['POST', 'GET'],
    'allowed_origins'          => ['http://localhost', 'http://127.0.0.1'],
    'allowed_origins_patterns' => [],
    'allowed_headers'          => ['Content-Type', 'X-API-Key'],
    'exposed_headers'          => [],
    'max_age'                  => 0,
    'supports_credentials'     => false,
];
