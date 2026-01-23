<?php

return [
    'stun' => explode(',', env('ICE_STUN_LIST', 'stun:stun.l.google.com:19302')),
    'turn' => [
        [
            'urls' => explode(',', env('ICE_TURN_URLS', '')),
            'username' => env('ICE_TURN_USERNAME', ''),
            'credential' => env('ICE_TURN_CREDENTIAL', ''),
        ],
    ],
];
