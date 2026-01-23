<?php

namespace App\Streaming;

use Illuminate\Support\Facades\Http;

class LivepeerClient
{
    protected string $baseUrl;

    public function __construct(string $baseUrl = 'https://livepeer.studio/api')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    protected function http(string $token)
    {
        return Http::withToken($token)->acceptJson()->retry(3, 200, throw: false);
    }

    public function createStream(string $name, string $token): array
    {
        $res = $this->http($token)->post($this->baseUrl . '/stream', [
            'name' => $name,
        ]);
        if (!$res->successful()) {
            return ['error' => $res->json()];
        }
        return $res->json();
    }

    public function getStream(string $id, string $token): array
    {
        $res = $this->http($token)->get($this->baseUrl . '/stream/' . $id);
        if (!$res->successful()) {
            return ['error' => $res->json()];
        }
        return $res->json();
    }

    public function terminateStream(string $id, string $token): array
    {
        $res = $this->http($token)->delete($this->baseUrl . '/stream/' . $id);
        if (!$res->successful()) {
            return ['error' => $res->json()];
        }
        return ['success' => true];
    }
}
