<?php

return [
    'provider' => env('TRANSCRIPTION_PROVIDER', 'none'), // whisper | gcloud | none
    'whisper' => [
        'endpoint' => env('WHISPER_ENDPOINT', 'https://api.openai.com/v1/audio/transcriptions'),
        'api_key' => env('WHISPER_API_KEY', ''),
        'language' => env('WHISPER_LANGUAGE', 'auto'),
    ],
    'gcloud' => [
        'endpoint' => env('GCLOUD_SPEECH_ENDPOINT', ''),
        'api_key' => env('GCLOUD_API_KEY', ''),
    ],
];
