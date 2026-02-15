<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'bank_name',
        'account_number',
        'account_holder',
        'is_active',
        'logo_url',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
