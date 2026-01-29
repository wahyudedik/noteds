<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StreamingProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'config', 'active',
    ];

    protected $casts = [
        'config' => 'array',
        'active' => 'boolean',
    ];

    // streams relation removed after Streams feature deprecation

    public function getConfigAttribute($value)
    {
        $arr = is_array($value) ? $value : (json_decode($value, true) ?: []);
        foreach (['api_token','stream_key','channel_arn'] as $key) {
            if (isset($arr[$key]) && is_string($arr[$key])) {
                try {
                    $arr[$key] = \Illuminate\Support\Facades\Crypt::decryptString($arr[$key]);
                } catch (\Throwable $e) {
                }
            }
        }
        return $arr;
    }

    public function setConfigAttribute($value)
    {
        $arr = is_array($value) ? $value : (json_decode($value, true) ?: []);
        foreach (['api_token','stream_key','channel_arn'] as $key) {
            if (isset($arr[$key]) && is_string($arr[$key])) {
                try {
                    $arr[$key] = \Illuminate\Support\Facades\Crypt::encryptString($arr[$key]);
                } catch (\Throwable $e) {
                }
            }
        }
        $this->attributes['config'] = json_encode($arr);
    }
}
