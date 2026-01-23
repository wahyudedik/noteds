<?php

return [
    'templates' => [
        'stream_started' => [
            'subject' => 'Stream Dimulai: :title',
            'body' => 'Stream ":title" telah dimulai.',
        ],
        'stream_ended' => [
            'subject' => 'Stream Berakhir: :title',
            'body' => 'Stream ":title" telah berakhir.',
        ],
    ],
    'defaults' => [
        'channels' => ['email' => true, 'in_app' => true],
        'frequency' => 'immediate',
    ],
];
