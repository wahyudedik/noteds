<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GamificationConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'key', 'points', 'enabled', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'enabled' => 'boolean',
            'meta' => 'array',
        ];
    }
}
