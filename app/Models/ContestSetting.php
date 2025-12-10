<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContestSetting extends Model
{
    protected $fillable = [
        'enabled',
        'platform_fee_percentage',
        'max_contests_per_buyer',
        'max_prize_amount',
        'require_kyc',
        'auto_distribute_prizes',
        'terms_and_conditions',
        'approval_guidelines',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'platform_fee_percentage' => 'float',
        'max_prize_amount' => 'float',
        'require_kyc' => 'boolean',
        'auto_distribute_prizes' => 'boolean',
    ];
}
