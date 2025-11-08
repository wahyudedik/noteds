<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Hashtag extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'posts_count',
    ];

    protected function casts(): array
    {
        return [
            'posts_count' => 'integer',
        ];
    }

    /**
     * Get posts that use this hashtag.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_hashtags', 'hashtag_id', 'post_id')
            ->withTimestamps();
    }

    /**
     * Generate slug from name.
     */
    public static function generateSlug(string $name): string
    {
        return Str::slug(strtolower($name));
    }

    /**
     * Find or create hashtag by name.
     */
    public static function findOrCreateByName(string $name): self
    {
        $slug = self::generateSlug($name);
        
        return self::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name]
        );
    }
}

