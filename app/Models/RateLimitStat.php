<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateLimitStat extends Model
{
    protected $fillable = ['endpoint', 'minute_bucket', 'count'];
}
