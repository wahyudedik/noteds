<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Message extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        if (!isset($this->attributes['id'])) {
            $this->attributes['id'] = (string) Str::uuid();
        }
    }

    protected $fillable = [
        'conversation_id',
        'user_id',
        'content',
        'type',
        'reply_to_id',
        'is_edited',
        'edited_at',
        'is_deleted',
        'deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'is_edited' => 'boolean',
            'edited_at' => 'datetime',
            'is_deleted' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the conversation.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the user who sent the message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the message this is replying to.
     */
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    /**
     * Get all media for this message.
     */
    public function media(): HasMany
    {
        return $this->hasMany(MessageMedia::class)->orderBy('order');
    }

    /**
     * Get all read receipts for this message.
     */
    public function readReceipts(): HasMany
    {
        return $this->hasMany(ReadReceipt::class);
    }

    /**
     * Check if message is read by user.
     */
    public function isReadBy(User $user): bool
    {
        return $this->readReceipts()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Get read receipt for user.
     */
    public function getReadReceiptFor(User $user): ?ReadReceipt
    {
        return $this->readReceipts()
            ->where('user_id', $user->id)
            ->first();
    }
}
