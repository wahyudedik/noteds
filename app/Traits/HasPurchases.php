<?php

namespace App\Traits;

trait HasPurchases
{
    /**
     * Check if user has purchased a specific note
     */
    public function hasPurchased(int $noteId): bool
    {
        return \DB::table('purchased_notes')
            ->where('user_id', $this->id)
            ->where('note_id', $noteId)
            ->exists();
    }

    /**
     * Get purchased notes
     */
    public function purchasedNotes()
    {
        return $this->belongsToMany(\App\Models\Note::class, 'purchased_notes')
            ->withTimestamps()
            ->withPivot('price', 'currency');
    }

    /**
     * Get viewed notes (recent viewing history)
     */
    public function viewedNotes()
    {
        return $this->belongsToMany(\App\Models\Note::class, 'note_view_history')
            ->withTimestamps()
            ->orderByDesc('note_view_history.created_at');
    }

    /**
     * Get notes created by user
     */
    public function createdNotes()
    {
        return $this->hasMany(\App\Models\Note::class);
    }
}
