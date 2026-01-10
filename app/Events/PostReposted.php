<?php

namespace App\Events;

use App\Models\Repost;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostReposted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Repost $repost
    ) {}
}
