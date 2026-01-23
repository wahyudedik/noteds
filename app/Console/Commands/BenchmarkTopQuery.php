<?php

namespace App\Console\Commands;

use App\Services\PostRankingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BenchmarkTopQuery extends Command
{
    protected $signature = 'bench:top {--periods=day,week,month,all} {--metrics=engagement,upvotes,mixed} {--perPage=15} {--tag=}';
    protected $description = 'Benchmark Top query performance';

    public function handle(PostRankingService $svc): int
    {
        $periods = explode(',', $this->option('periods'));
        $metrics = explode(',', $this->option('metrics'));
        $perPage = (int)$this->option('perPage');
        $rows = [];
        foreach ($periods as $p) {
            foreach ($metrics as $m) {
                $startMem = memory_get_usage(true);
                $t1 = microtime(true);
                $res = $svc->getTopPosts($p, $m, $perPage);
                // Force materialization
                $count = $res->count();
                $t2 = microtime(true);
                $endMem = memory_get_usage(true);
                $rows[] = [
                    'period' => $p,
                    'metric' => $m,
                    'count' => $count,
                    'query_time_ms' => round(($t2 - $t1) * 1000, 2),
                    'memory_diff_kb' => round(($endMem - $startMem) / 1024),
                ];
                $this->line(sprintf('%s/%s: %d items in %sms', $p, $m, $count, round(($t2 - $t1)*1000,2)));
            }
        }
        $tag = $this->option('tag');
        $file = $tag ? "benchmarks/{$tag}.json" : 'benchmarks/top_benchmark.json';
        Storage::put($file, json_encode($rows, JSON_PRETTY_PRINT));
        $csv = "period,metric,count,query_time_ms,memory_diff_kb\n";
        foreach ($rows as $r) {
            $csv .= implode(',', $r)."\n";
        }
        Storage::put('benchmarks/top_benchmark.csv', $csv);
        $this->info('Benchmark results saved to storage/app/benchmarks/');
        return 0;
    }
}
