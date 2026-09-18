<?php

declare(strict_types=1);

return [
    'api_key' => 'acomplicatedapikey',

    'ngrok' => [
        'api_key' => 'ngrok_key',
    ],

    'http' => [
        'connect_timeout' => 3,
        'timeout' => 5,

        'max_response_bytes' => 1024 * 1024, // 1 MB
    ],
];