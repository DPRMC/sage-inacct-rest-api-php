<?php

declare(strict_types=1);

return [
    'client_id' => env('SAGE_CLIENT_ID'),
    'client_secret' => env('SAGE_CLIENT_SECRET'),
    'username' => env('SAGE_USERNAME'),
    'scope' => env('SAGE_SCOPE'),
    'base_url' => env('SAGE_BASE_URL', 'https://api.intacct.com/ia/api/v1'),
    'token_endpoint' => env('SAGE_TOKEN_ENDPOINT', 'https://api.intacct.com/ia/api/v1/oauth2/token'),
    'authorization_endpoint' => env(
        'SAGE_AUTHORIZATION_ENDPOINT',
        'https://api.intacct.com/ia/api/v1/oauth2/authorize',
    ),
    'connection_name' => env('SAGE_CONNECTION_NAME', 'default'),
    'allow_writes' => filter_var(env('SAGE_ALLOW_WRITES', false), FILTER_VALIDATE_BOOLEAN),
];
