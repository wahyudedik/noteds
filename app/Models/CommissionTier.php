<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;

class CommissionTier extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'description',
        'volume_threshold',
        'platform_fee_percent',
        'creator_commission_percent',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'volume_threshold' => 'float',
            'platform_fee_percent' => 'float',
            'creator_commission_percent' => 'float',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}

