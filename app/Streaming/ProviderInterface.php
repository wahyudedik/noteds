<?php

namespace App\Streaming;

use App\Models\LiveStream;
use App\Models\StreamingProvider;

interface ProviderInterface
{
    public function start(LiveStream $stream, StreamingProvider $provider): array;
    public function end(LiveStream $stream, StreamingProvider $provider): array;
}
