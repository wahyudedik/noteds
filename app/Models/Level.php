<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'level_order',
        'description',
        'icon',
        'color',
        'commission_discount_percent',
        'priority_support',
        'early_access',
        'benefits',
        'criteria_type',
        'criteria_value',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'level_order' => 'integer',
            'commission_discount_percent' => 'decimal:2',
            'priority_support' => 'boolean',
            'early_access' => 'boolean',
            'criteria_value' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function userLevels(): HasMany
    {
        return $this->hasMany(UserLevel::class);
    }

    /**
     * Get benefits as array.
     */
    public function getBenefitsArrayAttribute(): array
    {
        if (empty($this->benefits)) {
            return [];
        }

        $decoded = json_decode($this->benefits, true);
        return is_array($decoded) ? $decoded : [$this->benefits];
    }
}
