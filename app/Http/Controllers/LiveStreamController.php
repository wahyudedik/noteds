<?php

namespace App\Http\Controllers;

use App\Models\LiveStream;
use App\Models\GroupMember;
use App\Models\StreamingProvider;
use App\Models\StreamLog;
use App\Streaming\AWSIVSProvider;
use App\Streaming\CustomHLSProvider;
use App\Streaming\LivepeerProvider;
use App\Streaming\ProviderInterface;
use App\Services\StreamNotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LiveStreamController extends Controller
{
    public function index()
    {
        $streams = LiveStream::orderByRaw("FIELD(status, 'live','scheduled','ended')")
            ->latest('started_at')
            ->paginate(12);
        return Inertia::render('Streams/Index', [
            'streams' => $streams,
        ]);
    }

    public function show(LiveStream $liveStream)
    {
        $liveStream->load(['user', 'chatMessages' => function ($q) {
            $q->latest()->limit(50);
        }]);
        return Inertia::render('Streams/Show', [
            'stream' => $liveStream,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
            'provider' => 'required|in:custom_hls,youtube,twitch',
            'ingest_url' => 'nullable|string|max:255',
            'stream_key' => 'nullable|string|max:255',
            'playback_url' => 'nullable|string|max:255',
            'streaming_provider_id' => 'nullable|exists:streaming_providers,id',
            'group_id' => 'nullable|exists:groups,id',
            'group_only' => 'nullable|boolean',
        ]);
        $validated['user_id'] = $request->user()->id;
        $validated['status'] = $validated['scheduled_at'] ? 'scheduled' : 'live';
        $stream = LiveStream::create($validated);
        return redirect()->route('streams.show', $stream->id)->with('success', 'Live stream created');
    }

    public function start(LiveStream $liveStream, StreamNotificationService $notifier)
    {
        $this->authorize('update', $liveStream);
        $liveStream->status = 'live';
        $liveStream->started_at = now();
        if ($liveStream->streaming_provider_id) {
            $providerModel = StreamingProvider::find($liveStream->streaming_provider_id);
            $adapter = $this->adapterFor($providerModel->type);
            $adapter->start($liveStream, $providerModel);
            StreamLog::create([
                'live_stream_id' => $liveStream->id,
                'provider' => $providerModel->type,
                'level' => 'info',
                'message' => 'stream_started',
                'context' => ['provider' => $providerModel->config],
            ]);
        }
        $liveStream->save();
        $notifier->notifyStarted($liveStream);
        return back()->with('success', 'Stream started');
    }

    public function end(LiveStream $liveStream, StreamNotificationService $notifier)
    {
        $this->authorize('update', $liveStream);
        $liveStream->status = 'ended';
        $liveStream->ended_at = now();
        if ($liveStream->streaming_provider_id) {
            $providerModel = StreamingProvider::find($liveStream->streaming_provider_id);
            $adapter = $this->adapterFor($providerModel->type);
            $adapter->end($liveStream, $providerModel);
            StreamLog::create([
                'live_stream_id' => $liveStream->id,
                'provider' => $providerModel->type,
                'level' => 'info',
                'message' => 'stream_ended',
                'context' => ['provider' => $providerModel->config],
            ]);
        }
        $liveStream->save();
        $notifier->notifyEnded($liveStream);
        return back()->with('success', 'Stream ended');
    }

    protected function adapterFor(string $type): ProviderInterface
    {
        return match ($type) {
            'aws_ivs' => new AWSIVSProvider(),
            'livepeer' => new LivepeerProvider(),
            default => new CustomHLSProvider(),
        };
    }
}
