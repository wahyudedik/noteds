<?php

namespace App\Services;

use App\Models\CallMetric;
use Illuminate\Support\Facades\Mail;

class CallMetricsService
{
    public function record(array $data): void
    {
        CallMetric::create($data);
        $lat = (int) ($data['latency_ms'] ?? 0);
        $loss = (float) ($data['packet_loss_pct'] ?? 0);
        $jit = (int) ($data['jitter_ms'] ?? 0);
        $th = config('monitoring.thresholds');
        if ($lat > ($th['latency_ms'] ?? 800) || $loss > ($th['packet_loss_pct'] ?? 2.0) || $jit > ($th['jitter_ms'] ?? 100)) {
            $to = config('monitoring.alert_email');
            if ($to) {
                Mail::raw('Call metrics exceeded thresholds: latency '.$lat.'ms, loss '.$loss.'%, jitter '.$jit.'ms', function ($m) use ($to) {
                    $m->to($to)->subject('Call Metrics Alert');
                });
            }
        }
    }
}
