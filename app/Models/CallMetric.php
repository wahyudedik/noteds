<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'call_session_id', 'user_id', 'latency_ms', 'packet_loss_pct', 'jitter_ms',
    ];
}
