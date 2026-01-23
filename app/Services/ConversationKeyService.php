<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationKey;
use App\Models\ConversationKeyAccessLog;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class ConversationKeyService
{
    public function ensureKey(Conversation $conversation): ConversationKey
    {
        $key = ConversationKey::where('conversation_id', $conversation->id)
            ->orderByDesc('version')
            ->first();
        if ($key) return $key;
        return $this->rotateKey($conversation);
    }

    public function rotateKey(Conversation $conversation): ConversationKey
    {
        $latest = ConversationKey::where('conversation_id', $conversation->id)
            ->orderByDesc('version')->first();
        $version = $latest ? ($latest->version + 1) : 1;
        $raw = random_bytes(32);
        $b64 = base64_encode($raw);
        $encrypted = Crypt::encryptString($b64);
        $key = ConversationKey::create([
            'id' => (string) Str::uuid(),
            'conversation_id' => $conversation->id,
            'version' => $version,
            'algorithm' => 'AES-GCM',
            'encrypted_key' => $encrypted,
            'rotated_at' => now(),
        ]);
        return $key;
    }

    public function fetchForUser(Conversation $conversation, $user, string $ip = null, string $ua = null): array
    {
        if (!$conversation->hasParticipant($user)) {
            throw new \Exception('Unauthorized');
        }
        $key = $this->ensureKey($conversation);
        ConversationKeyAccessLog::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'action' => 'fetch',
            'ip' => $ip,
            'user_agent' => $ua,
        ]);
        return [
            'version' => $key->version,
            'algorithm' => $key->algorithm,
            'key_b64_encrypted' => $key->encrypted_key,
        ];
    }
}
