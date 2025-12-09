<?php

namespace App\Models;

use App\Models\AffiliatePromotionalMaterial;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AffiliateLink extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'affiliate_id',
        'code',
        'name',
        'description',
        'destination_url',
        'landing_page_content',
        'landing_page_slug',
        'clicks',
        'conversions',
        'total_commission',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'clicks' => 'integer',
            'conversions' => 'integer',
            'total_commission' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Generate unique affiliate code.
     */
    public static function generateUniqueCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Get full affiliate URL.
     */
    public function getFullUrlAttribute(): string
    {
        $baseUrl = config('app.url');
        $destination = $this->destination_url ?: route('marketplace.index');
        return $baseUrl . '/?ref=' . $this->code;
    }

    /**
     * Get conversion rate.
     */
    public function getConversionRateAttribute(): float
    {
        if ($this->clicks === 0) {
            return 0;
        }

        return round(($this->conversions / $this->clicks) * 100, 2);
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'affiliate_id');
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(AffiliateConversion::class);
    }

    /**
     * Get all commissions for this affiliate link (through conversions)
     */
    public function commissions()
    {
        return $this->hasManyThrough(AffiliateCommission::class, AffiliateConversion::class);
    }

    public function promotionalMaterials(): HasMany
    {
        return $this->hasMany(AffiliatePromotionalMaterial::class);
    }

    /**
     * Get the landing page assigned to this link.
     */
    public function landingPage()
    {
        return $this->belongsToMany(
            UserLandingPage::class,
            'affiliate_link_user_landing_page',
            'affiliate_link_id',
            'user_landing_page_id'
        )->first();
    }

    /**
     * Get landing page URL.
     */
    public function getLandingPageUrlAttribute(): ?string
    {
        if (!$this->landing_page_slug) {
            return null;
        }

        return route('affiliate.landing', $this->landing_page_slug);
    }
}
