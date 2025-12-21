<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = Post::all();
        $users = User::all();

        foreach ($posts as $post) {
            // Create 2-5 top-level comments per post
            $numComments = rand(2, 5);

            $baseCreatedAt = $post->created_at->copy();

            for ($i = 0; $i < $numComments; $i++) {
                $user = $users->random();
                $createdAt = $baseCreatedAt->copy()->addHours(rand(1, 24 * ($i + 1)));

                $comment = Comment::create([
                    'user_id' => $user->id,
                    'post_id' => $post->id,
                    'parent_id' => null,
                    'content' => $this->getCommentContent($post->purpose_type),
                    'upvotes_count' => rand(0, 10),
                    'downvotes_count' => rand(0, 2),
                    'is_best_answer' => $i === 0 && $post->purpose_type === 'ask_question' && rand(0, 1),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                // Create 0-3 replies for some comments
                if (rand(0, 1)) {
                    $numReplies = rand(0, 3);
                    $replyBaseTime = $createdAt->copy();

                    for ($j = 0; $j < $numReplies; $j++) {
                        $replyUser = $users->random();
                        $replyCreatedAt = $replyBaseTime->copy()->addHours(rand(1, 12 * ($j + 1)));

                        Comment::create([
                            'user_id' => $replyUser->id,
                            'post_id' => $post->id,
                            'parent_id' => $comment->id,
                            'content' => $this->getReplyContent(),
                            'upvotes_count' => rand(0, 5),
                            'downvotes_count' => rand(0, 1),
                            'is_best_answer' => false,
                            'created_at' => $replyCreatedAt,
                            'updated_at' => $replyCreatedAt,
                        ]);
                    }
                }
            }

            // Update post comments_count
            $post->comments_count = Comment::where('post_id', $post->id)->count();
            $post->save();
        }
    }

    private function getCommentContent(string $purposeType): string
    {
        $contents = [
            'idea_business' => [
                'Ide yang menarik! Saya tertarik untuk berkolaborasi. Bisa diskusi lebih lanjut?',
                'Konsepnya bagus, tapi perlu pertimbangkan beberapa hal: market size, competition, dan monetization strategy.',
                'Saya punya pengalaman serupa. Bisa share lebih detail tentang target market?',
            ],
            'ask_question' => [
                'Berdasarkan pengalaman saya, cara terbaik adalah...',
                'Saya pernah menghadapi masalah serupa. Solusinya adalah...',
                'Untuk pertanyaan ini, saya sarankan untuk...',
            ],
            'share_experience' => [
                'Terima kasih sudah sharing! Pengalaman yang sangat berharga.',
                'Saya juga mengalami hal serupa. Setuju dengan poin-poin yang disebutkan.',
                'Inspiring! Bisa share lebih detail tentang...',
            ],
            'find_partner' => [
                'Saya tertarik! Bisa diskusi lebih lanjut tentang opportunity ini?',
                'Saya memiliki expertise di bidang tersebut. Open untuk collaboration.',
                'Bisa share lebih detail tentang business model dan equity structure?',
            ],
            'find_tools' => [
                'Saya rekomendasikan menggunakan... karena...',
                'Berdasarkan pengalaman, tools terbaik untuk ini adalah...',
                'Saya sudah mencoba beberapa tools, yang paling efektif adalah...',
            ],
            'validate_idea' => [
                'Ide ini sangat layak untuk dikembangkan! Market potential-nya besar.',
                'Perlu pertimbangkan beberapa hal: competition, market size, dan differentiation.',
                'Saya pikir ide ini bagus, tapi perlu validasi lebih lanjut dengan target customers.',
            ],
        ];

        $options = $contents[$purposeType] ?? ['Komentar yang menarik!'];
        return $options[array_rand($options)];
    }

    private function getReplyContent(): string
    {
        $replies = [
            'Setuju dengan pendapat di atas!',
            'Terima kasih atas informasinya!',
            'Bisa elaborasi lebih detail?',
            'Saya punya pertanyaan tambahan...',
            'Menambahkan dari komentar sebelumnya...',
            'Poin yang bagus!',
        ];

        return $replies[array_rand($replies)];
    }
}
