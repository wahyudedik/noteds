<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserLandingPage extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'user_landing_pages';

    protected $fillable = [
        'user_id',
        'slug',
        'content',
    ];

    /**
     * Get the user that owns the landing page.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the affiliate links assigned to this landing page.
     */
    public function affiliateLinks(): BelongsToMany
    {
        return $this->belongsToMany(
            AffiliateLink::class,
            'affiliate_link_user_landing_page',
            'user_landing_page_id',
            'affiliate_link_id'
        );
    }
}
