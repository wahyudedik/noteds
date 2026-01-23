<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CompareBenchmarks extends Command
{
    protected $signature = 'bench:compare {before} {after}';
    protected $description = 'Compare two benchmark result files and produce diff';

    public function handle(): int
    {
        $before = $this->argument('before');
        $after = $this->argument('after');
        $bPath = "benchmarks/{$before}.json";
        $aPath = "benchmarks/{$after}.json";
        if (!Storage::exists($bPath) || !Storage::exists($aPath)) {
            $this->error('Benchmark files not found.');
            return 1;
        }
        $b = json_decode(Storage::get($bPath), true) ?: [];
        $a = json_decode(Storage::get($aPath), true) ?: [];
        $diff = [];
        foreach ($a as $row) {
            $key = $row['period'].'/'.$row['metric'];
            $beforeRow = collect($b)->first(fn($r) => ($r['period'].'/'.$r['metric']) === $key);
            if (!$beforeRow) continue;
            $diff[] = [
                'key' => $key,
                'query_time_ms_before' => $beforeRow['query_time_ms'],
                'query_time_ms_after' => $row['query_time_ms'],
                'improvement_ms' => $beforeRow['query_time_ms'] - $row['query_time_ms'],
                'improvement_pct' => $beforeRow['query_time_ms'] > 0 ? round(100 * ($beforeRow['query_time_ms'] - $row['query_time_ms']) / $beforeRow['query_time_ms'], 2) : 0,
            ];
        }
        Storage::put('benchmarks/top_compare.json', json_encode($diff, JSON_PRETTY_PRINT));
        $this->info('Comparison saved to storage/app/benchmarks/top_compare.json');
        return 0;
    }
}
