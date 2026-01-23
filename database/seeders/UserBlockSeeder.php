<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UserBlockSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::inRandomOrder()->get();
        foreach ($users as $blocker) {
            $blockedCandidates = $users->shuffle()->take(rand(0,2));
            foreach ($blockedCandidates as $blocked) {
                if ($blocker->id === $blocked->id) continue;
                DB::table('user_blocks')->updateOrInsert(
                    ['blocker_id' => $blocker->id, 'blocked_id' => $blocked->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }
}
