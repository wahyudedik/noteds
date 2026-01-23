<?php

namespace App\Streaming;

use App\Models\LiveStream;
use App\Models\StreamingProvider;

class CustomHLSProvider implements ProviderInterface
{
    public function start(LiveStream $stream, StreamingProvider $provider): array
    {
        $cfg = $provider->config ?? [];
        $stream->ingest_url = $cfg['ingest_url'] ?? $stream->ingest_url;
        $stream->stream_key = $cfg['stream_key'] ?? $stream->stream_key;
        $stream->playback_url = $cfg['playback_url'] ?? $stream->playback_url;
        return ['status' => 'ok'];
    }

    public function end(LiveStream $stream, StreamingProvider $provider): array
    {
        return ['status' => 'ok'];
    }
}
