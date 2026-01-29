<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class LimiterList extends Command
{
    protected $signature = 'limiter:list';
    protected $description = 'List active named limiters';

    public function handle(): int
    {
        $names = ['search', 'chat', 'analytics'];
        foreach ($names as $n) {
            $v = Cache::get("rate_limit:$n");
            $this->line(($v ? json_encode($v) : 'default') . " [$n]");
        }
        return self::SUCCESS;
    }
}
