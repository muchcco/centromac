<?php

return [
    'shared_secret'          => env('SSO_SHARED_SECRET', ''),
    'expected_issuer'        => env('SSO_EXPECTED_ISSUER', env('SSO_ISSUER', '')),
    'expected_audience'      => env('SSO_EXPECTED_AUDIENCE', env('SSO_AUDIENCE', '')),
    'auth_server_login_url'  => env('AUTH_SERVER_LOGIN_URL', 'https://sismac.mac.pe/auth/public/login'),
    'auth_server_logout_base_url' => env('AUTH_SERVER_LOGOUT_BASE_URL', 'https://sismac.mac.pe/auth/public/logout'),
    // Solo para logout: a dónde vuelve el usuario tras cerrar sesión en el Auth Server
    'auth_server_logout_next' => env('AUTH_SERVER_LOGOUT_NEXT', ''),
    // Callback de login: a dónde redirige el Auth Server tras autenticar exitosamente
    'callback_url'           => env('SSO_CALLBACK_URL', ''),
];
