<?php

namespace App\Streaming;

use App\Models\LiveStream;
use App\Models\StreamingProvider;
use App\Models\StreamLog;

class LivepeerProvider implements ProviderInterface
{
    public function start(LiveStream $stream, StreamingProvider $provider): array
    {
        $cfg = $provider->config ?? [];
        $client = new LivepeerClient($cfg['base_url'] ?? 'https://livepeer.studio/api');
        if (!empty($cfg['api_token'])) {
            $created = $client->createStream($stream->title, $cfg['api_token']);
            if (!isset($created['error'])) {
                $stream->ingest_url = $created['ingest']['url'] ?? $stream->ingest_url;
                $stream->stream_key = $created['streamKey'] ?? $stream->stream_key;
                $stream->playback_url = $created['playbackUrl'] ?? $stream->playback_url;
                StreamLog::create(['live_stream_id' => $stream->id, 'provider' => 'livepeer', 'level' => 'info', 'message' => 'livepeer_create_stream', 'context' => $created]);
                return ['status' => 'ok', 'data' => $created];
            }
            StreamLog::create(['live_stream_id' => $stream->id, 'provider' => 'livepeer', 'level' => 'error', 'message' => 'livepeer_error', 'context' => $created['error']]);
            return ['error' => $created['error']];
        }
        return ['warning' => 'missing_livepeer_token'];
    }

    public function end(LiveStream $stream, StreamingProvider $provider): array
    {
        $cfg = $provider->config ?? [];
        if (!empty($cfg['api_token']) && !empty($cfg['stream_id'])) {
            $client = new LivepeerClient($cfg['base_url'] ?? 'https://livepeer.studio/api');
            $terminated = $client->terminateStream($cfg['stream_id'], $cfg['api_token']);
            StreamLog::create(['live_stream_id' => $stream->id, 'provider' => 'livepeer', 'level' => 'info', 'message' => 'livepeer_terminate_stream', 'context' => $terminated]);
            return $terminated;
        }
        return ['status' => 'noop'];
    }
}
