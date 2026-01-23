<?php

return [
    'engine' => env('WAVEFORM_ENGINE', 'default'), // ffmpeg | default
    'ffmpeg_path' => env('FFMPEG_PATH', ''),
    'ffprobe_path' => env('FFPROBE_PATH', ''),
];
