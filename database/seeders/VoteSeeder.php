<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostVote;
use App\Models\Comment;
use App\Models\CommentVote;
use App\Models\User;
use Illuminate\Database\Seeder;

class VoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $posts = Post::all();
        $comments = Comment::all();

        // Seed Post Votes
        foreach ($posts as $post) {
            // Get random users to vote (30-70% of users)
            $numVoters = rand((int)($users->count() * 0.3), (int)($users->count() * 0.7));
            $voters = $users->random(min($numVoters, $users->count()));

            $upvotes = 0;
            $downvotes = 0;

            foreach ($voters as $user) {
                // Skip if user is the post owner
                if ($user->id === $post->user_id) {
                    continue;
                }

                // 80% upvote, 20% downvote
                $voteType = rand(1, 10) <= 8 ? 'upvote' : 'downvote';

                PostVote::create([
                    'user_id' => $user->id,
                    'post_id' => $post->id,
                    'vote_type' => $voteType,
                ]);

                if ($voteType === 'upvote') {
                    $upvotes++;
                } else {
                    $downvotes++;
                }
            }

            // Update post vote counts
            $post->upvotes_count = $upvotes;
            $post->downvotes_count = $downvotes;
            $post->save();
        }

        // Seed Comment Votes
        foreach ($comments as $comment) {
            // Get random users to vote (20-50% of users)
            $numVoters = rand((int)($users->count() * 0.2), (int)($users->count() * 0.5));
            $voters = $users->random(min($numVoters, $users->count()));

            $upvotes = 0;
            $downvotes = 0;

            foreach ($voters as $user) {
                // Skip if user is the comment owner
                if ($user->id === $comment->user_id) {
                    continue;
                }

                // 85% upvote, 15% downvote
                $voteType = rand(1, 20) <= 17 ? 'upvote' : 'downvote';

                CommentVote::create([
                    'user_id' => $user->id,
                    'comment_id' => $comment->id,
                    'vote_type' => $voteType,
                ]);

                if ($voteType === 'upvote') {
                    $upvotes++;
                } else {
                    $downvotes++;
                }
            }

            // Update comment vote counts
            $comment->upvotes_count = $upvotes;
            $comment->downvotes_count = $downvotes;
            $comment->save();
        }
    }
}
