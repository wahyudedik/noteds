<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LimiterUpdate extends Command
{
    protected $signature = 'limiter:update {--name=} {--limit=} {--duration=minute} {--reset}';
    protected $description = 'Update named limiter settings';

    public function handle(): int
    {
        $name = (string) $this->option('name');
        $reset = (bool) $this->option('reset');
        if (!$name) {
            $this->error('Missing --name');
            return self::FAILURE;
        }
        $valid = in_array($name, ['search', 'chat', 'analytics']);
        if (!$valid) {
            $this->error('Invalid limiter name');
            return self::FAILURE;
        }
        $key = "rate_limit:$name";
        if ($reset) {
            Cache::forget($key);
            $this->info("Reset $name to default");
            Log::channel('rate_limit')->warning('limiter_reset', ['name' => $name, 'ts' => now()->toIso8601String()]);
            return self::SUCCESS;
        }
        $limit = (int) $this->option('limit');
        $duration = (string) $this->option('duration') ?: 'minute';
        if ($limit <= 0) {
            $this->error('Invalid --limit');
            return self::FAILURE;
        }
        Cache::put($key, ['limit' => $limit, 'duration' => $duration, 'updated_at' => now()->toIso8601String()], 3600);
        $this->info("Updated $name: $limit per $duration");
        Log::channel('rate_limit')->warning('limiter_update', ['name' => $name, 'limit' => $limit, 'duration' => $duration, 'ts' => now()->toIso8601String()]);
        return self::SUCCESS;
    }
}
