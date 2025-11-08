<?php

namespace Database\Seeders;

use App\Models\Follow;
use App\Models\Hashtag;
use App\Models\Post;
use App\Models\PostBookmark;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\PostMedia;
use App\Models\PostReport;
use App\Models\PostView;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class SocialFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::with('notes')->get();

        if ($users->isEmpty()) {
            return;
        }

        $buyers = $users->filter(fn (User $user) => $user->hasRole('buyer'));
        $sellers = $users->filter(fn (User $user) => $user->hasRole('seller'));

        $this->seedFollows($buyers, $sellers);
        $this->seedPosts($sellers, $buyers);
    }

    protected function seedFollows($buyers, $sellers): void
    {
        foreach ($buyers as $buyer) {
            $targets = $sellers->random(min(3, $sellers->count()));

            foreach ($targets as $target) {
                Follow::firstOrCreate([
                    'follower_id' => $buyer->id,
                    'following_id' => $target->id,
                ]);
            }
        }
    }

    protected function seedPosts($sellers, $buyers): void
    {
        $hashtags = collect(['Laravel', 'AI', 'Marketing', 'Productivity', 'Learning']);

        foreach ($sellers->take(8) as $seller) {
            foreach (range(1, 2) as $index) {
                $note = $seller->notes()->inRandomOrder()->first();
                $content = $this->postContent($seller->name, $index);

                $post = Post::updateOrCreate(
                    [
                        'user_id' => $seller->id,
                        'content' => $content,
                    ],
                    [
                        'note_id' => $note?->id,
                        'parent_id' => null,
                        'is_pinned' => $index === 1,
                        'is_hidden' => false,
                        'hidden_at' => null,
                        'visibility' => Arr::random(['public', 'followers']),
                        'likes_count' => 0,
                        'comments_count' => 0,
                        'shares_count' => rand(0, 6),
                        'views_count' => rand(40, 200),
                        'is_published' => true,
                        'scheduled_at' => null,
                        'published_at' => now()->subDays(rand(0, 3))->setTime(rand(8, 20), rand(0, 59)),
                    ]
                );

                $this->attachHashtags($post, $hashtags);
                $this->attachMedia($post);
                $likes = $this->seedPostLikes($post, $buyers);
                $comments = $this->seedPostComments($post, $buyers, $seller);
                $this->seedPostBookmarks($post, $buyers);
                $this->seedPostViews($post, $buyers);
                $this->maybeReportPost($post, $buyers);

                $post->updateQuietly([
                    'likes_count' => $likes,
                    'comments_count' => $comments,
                ]);
            }
        }
    }

    protected function postContent(string $sellerName, int $index): string
    {
        return match ($index) {
            1 => "Halo semuanya! Aku baru saja update catatan premium dengan studi kasus terbaru. Ada yang lagi cari materi {$sellerName}? 😊",
            2 => "Tips cepat jual catatan di Noteds: fokus pada value, gunakan preview menarik, dan jangan lupa aktif di komunitas!",
            default => "Senang bisa berbagi insight seputar marketplace catatan digital!",
        };
    }

    protected function attachHashtags(Post $post, $hashtags): void
    {
        $tags = $hashtags->random(rand(2, 3))->map(function (string $name) {
            return Hashtag::firstOrCreate(
                ['slug' => Hashtag::generateSlug($name)],
                ['name' => $name]
            );
        });

        $post->hashtags()->syncWithoutDetaching($tags->pluck('id')->all());
    }

    protected function attachMedia(Post $post): void
    {
        if ($post->media()->exists()) {
            return;
        }

        PostMedia::create([
            'post_id' => $post->id,
            'file_path' => 'posts/media/' . $post->id . '.png',
            'file_type' => 'image',
            'mime_type' => 'image/png',
            'file_size' => rand(120_000, 450_000),
            'order' => 0,
        ]);
    }

    protected function seedPostLikes(Post $post, $buyers): int
    {
        $likers = $buyers->random(min(4, max(1, $buyers->count())));

        foreach ($likers as $buyer) {
            PostLike::firstOrCreate([
                'post_id' => $post->id,
                'user_id' => $buyer->id,
            ]);
        }

        return $post->likes()->count();
    }

    protected function seedPostComments(Post $post, $buyers, User $seller): int
    {
        if ($post->comments()->exists()) {
            return $post->comments()->count();
        }

        $commenters = $buyers->random(min(3, max(1, $buyers->count())));
        foreach ($commenters as $buyer) {
            $comment = PostComment::create([
                'post_id' => $post->id,
                'user_id' => $buyer->id,
                'content' => Arr::random([
                    'Thanks udah share tipsnya! Sangat membantu.',
                    'Catatannya keren banget, kebetulan lagi butuh materi ini.',
                    'Boleh dong share preview halamannya?',
                ]),
                'likes_count' => rand(0, 4),
            ]);

            // Seller replies to first comment
            if ($commenters->first()->id === $buyer->id) {
                PostComment::create([
                    'post_id' => $post->id,
                    'user_id' => $seller->id,
                    'parent_id' => $comment->id,
                    'content' => 'Terima kasih! Preview lengkapnya ada di marketplace ya 😊',
                    'likes_count' => rand(0, 2),
                ]);
            }
        }

        return $post->allComments()->count();
    }

    protected function seedPostBookmarks(Post $post, $buyers): void
    {
        foreach ($buyers->random(min(2, max(1, $buyers->count()))) as $buyer) {
            PostBookmark::firstOrCreate([
                'post_id' => $post->id,
                'user_id' => $buyer->id,
            ]);
        }
    }

    protected function seedPostViews(Post $post, $buyers): void
    {
        $viewers = $buyers->random(min(5, max(1, $buyers->count())));

        foreach ($viewers as $viewer) {
            $viewedDate = now()->subDays(rand(0, 3))->toDateString();
            $hash = sha1($post->id . '|' . $viewer->id . '|' . $viewedDate);

            PostView::firstOrCreate(
                [
                    'post_id' => $post->id,
                    'viewer_hash' => $hash,
                    'viewed_date' => $viewedDate,
                ],
                [
                    'user_id' => $viewer->id,
                    'ip_address' => '114.5.' . rand(0, 255) . '.' . rand(0, 255),
                    'user_agent' => Arr::random($this->userAgents()),
                    'viewed_at' => now()->subHours(rand(1, 48)),
                ]
            );
        }
    }

    protected function maybeReportPost(Post $post, $buyers): void
    {
        if (rand(0, 5) !== 0) {
            return;
        }

        $reporter = $buyers->random();

        PostReport::updateOrCreate(
            [
                'post_id' => $post->id,
                'user_id' => $reporter->id,
            ],
            [
                'reason' => Arr::random(['spam', 'inappropriate', 'other']),
                'description' => 'Simulasi laporan komunitas untuk testing modul moderasi.',
                'status' => 'pending',
            ]
        );
    }

    protected function userAgents(): array
    {
        return [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_2) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.3 Safari/605.1.15',
        ];
    }
}


