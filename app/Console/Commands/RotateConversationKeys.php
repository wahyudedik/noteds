<?php

namespace App\Console\Commands;

use App\Models\Conversation;
use App\Models\ConversationKey;
use App\Services\ConversationKeyService;
use Illuminate\Console\Command;

class RotateConversationKeys extends Command
{
    protected $signature = 'conversations:keys:rotate';
    protected $description = 'Rotate conversation encryption keys based on configured interval';

    public function handle(ConversationKeyService $service): int
    {
        $days = (int) config('conversation_keys.rotation_days', 30);
        $threshold = now()->subDays($days);
        $conversations = ConversationKey::where('rotated_at', '<', $threshold)->pluck('conversation_id')->unique()->all();
        foreach ($conversations as $cid) {
            $conv = Conversation::find($cid);
            if ($conv) {
                $service->rotateKey($conv);
            }
        }
        return self::SUCCESS;
    }
}
