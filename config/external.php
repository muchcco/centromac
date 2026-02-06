<?php

// config/external.php
return [
    'mac' => [
        'base_url' => env('EXTERNAL_MAC_BASE_URL'),
        'personal' => '/external-mac/personal',
        'assists'  => '/external-mac/assists',
        'formdata' => '/external-mac/formdata',
        'login_url' => env('AUTH_SERVER_LOGIN_URL'),
    ],
];
