<?php

return [
    'shared_secret'          => env('SSO_SHARED_SECRET', ''),
    'expected_issuer'        => env('SSO_EXPECTED_ISSUER', env('SSO_ISSUER', '')),
    'expected_audience'      => env('SSO_EXPECTED_AUDIENCE', env('SSO_AUDIENCE', '')),
    'auth_server_login_url'  => env('AUTH_SERVER_LOGIN_URL', 'https://sismac.mac.pe/auth/public/login'),
    'auth_server_logout_base_url' => env('AUTH_SERVER_LOGOUT_BASE_URL', 'https://sismac.mac.pe/auth/public/logout'),
    'auth_server_logout_next' => env('AUTH_SERVER_LOGOUT_NEXT', 'https://sismac.mac.pe/centromac/public/login?noauto=1'),
];
