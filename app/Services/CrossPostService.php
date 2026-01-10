<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Product;
use App\Models\Campaign;
use Illuminate\Support\Facades\DB;

class CrossPostService
{
    /**
     * Cross-post a post to marketplace (create a product).
     *
     * @param Post $post
     * @param array $productData
     * @return Product
     */
    public function crossPostToMarketplace(Post $post, array $productData): Product
    {
        $product = Product::create([
            'user_id' => $post->user_id,
            'name' => $productData['name'] ?? $post->title,
            'description' => $productData['description'] ?? $post->content,
            'price' => $productData['price'],
            'category' => $productData['category'] ?? 'other',
            'post_id' => $post->id, // Link back to original post
            'is_active' => true,
        ]);

        // Copy media if any
        if ($post->media->isNotEmpty()) {
            foreach ($post->media as $media) {
                // Copy media file logic would go here
                // This depends on your storage setup
            }
        }

        return $product;
    }

    /**
     * Cross-post a post to clipper (create a campaign reference).
     *
     * @param Post $post
     * @param Campaign $campaign
     * @return void
     */
    public function crossPostToClipper(Post $post, Campaign $campaign): void
    {
        // Link post to campaign (this might already be done via campaign_id)
        // This is more of a reference/link operation
        if (!$post->campaign_id) {
            $post->update(['campaign_id' => $campaign->id]);
        }
    }

    /**
     * Get cross-posted products for a post.
     *
     * @param Post $post
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCrossPostedProducts(Post $post)
    {
        return Product::where('post_id', $post->id)->get();
    }

    /**
     * Get cross-posted campaigns for a post.
     *
     * @param Post $post
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCrossPostedCampaigns(Post $post)
    {
        if (!$post->campaign_id) {
            return collect([]);
        }

        return Campaign::where('id', $post->campaign_id)->get();
    }
}

