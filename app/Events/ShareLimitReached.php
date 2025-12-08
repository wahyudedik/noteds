<?php

namespace App\Events;

use App\Models\User;
use App\Models\Note;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShareLimitReached
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public Note $note,
        public int $maxLimit
    ) {}
}
