<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Certification extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'icon',
        'color',
        'requirements',
        'benefits',
        'requires_application',
        'requires_approval',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'requirements' => 'array',
            'requires_application' => 'boolean',
            'requires_approval' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($certification) {
            if (empty($certification->slug)) {
                $certification->slug = Str::slug($certification->name);
            }
        });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_certifications')
            ->withPivot('status', 'application_notes', 'admin_notes', 'approved_by', 'applied_at', 'approved_at', 'rejected_at', 'expires_at', 'evidence')
            ->withTimestamps();
    }

    public function userCertifications(): HasMany
    {
        return $this->hasMany(UserCertification::class);
    }

    /**
     * Get active certifications
     */
    public static function active()
    {
        return static::where('is_active', true);
    }

    /**
     * Get certifications by category
     */
    public static function byCategory(string $category)
    {
        return static::where('category', $category)->where('is_active', true);
    }

    /**
     * Check if user has this certification (approved)
     */
    public function hasUser(User $user): bool
    {
        return $this->userCertifications()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->exists();
    }

    /**
     * Get user certification record
     */
    public function getUserCertification(User $user): ?UserCertification
    {
        return $this->userCertifications()
            ->where('user_id', $user->id)
            ->first();
    }
}

