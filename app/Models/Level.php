<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Level extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (!isset($this->attributes['id'])) {
            $this->attributes['id'] = (string) Str::uuid();
        }
    }

    protected $fillable = [
        'name',
        'min_points',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'min_points' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function userLevels(): HasMany
    {
        return $this->hasMany(UserLevel::class);
    }
}
