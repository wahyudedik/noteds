<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Conversation extends Model
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
        'type',
        'name',
        'description',
        'avatar',
        'created_by',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    /**
     * Get the user who created the conversation.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all messages in the conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get all participants in the conversation.
     */
    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    /**
     * Get active participants (not left).
     */
    public function activeParticipants(): HasMany
    {
        return $this->participants()->whereNull('left_at');
    }

    /**
     * Get the other user in a direct conversation.
     */
    public function getOtherUser(?User $currentUser = null): ?User
    {
        if ($this->type !== 'direct' || !$currentUser) {
            return null;
        }

        $participant = $this->activeParticipants()
            ->where('user_id', '!=', $currentUser->id)
            ->with('user')
            ->first();

        return $participant ? $participant->user : null;
    }

    /**
     * Get unread count for a user.
     */
    public function getUnreadCount(User $user): int
    {
        $participant = $this->participants()
            ->where('user_id', $user->id)
            ->first();

        if (!$participant || !$participant->last_read_at) {
            return $this->messages()->count();
        }

        return $this->messages()
            ->where('created_at', '>', $participant->last_read_at)
            ->where('user_id', '!=', $user->id)
            ->count();
    }

    /**
     * Check if user is participant.
     */
    public function hasParticipant(User $user): bool
    {
        return $this->activeParticipants()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Get conversation name for display.
     */
    public function getDisplayName(?User $currentUser = null): string
    {
        if ($this->type === 'group' && $this->name) {
            return $this->name;
        }

        if ($this->type === 'direct' && $currentUser) {
            $otherUser = $this->getOtherUser($currentUser);
            return $otherUser ? $otherUser->name : 'Unknown User';
        }

        return 'Conversation';
    }

    /**
     * Get conversation avatar for display.
     */
    public function getDisplayAvatar(?User $currentUser = null): ?string
    {
        if ($this->type === 'group' && $this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        if ($this->type === 'direct' && $currentUser) {
            $otherUser = $this->getOtherUser($currentUser);
            return $otherUser ? $otherUser->avatar_url : null;
        }

        return null;
    }
}
