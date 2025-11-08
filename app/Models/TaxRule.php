<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRule extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'country_code',
        'country_name',
        'note_category',
        'tax_percent',
        'is_inclusive',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tax_percent' => 'decimal:2',
            'is_inclusive' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}

