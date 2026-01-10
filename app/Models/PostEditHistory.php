<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\HasUuid;

class PostEditHistory extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'post_edit_histories';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'post_id',
        'user_id',
        'title',
        'content',
        'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'edited_at' => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
