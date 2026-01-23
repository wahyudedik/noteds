<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class GDPRSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::inRandomOrder()->limit(10)->get();
        foreach ($users as $u) {
            DB::table('privacy_consents')->updateOrInsert(
                ['user_id' => $u->id, 'policy_version' => '1.0'],
                [
                    'cookie_categories' => json_encode(['functional' => rand(0,1) === 1, 'analytics' => true, 'marketing' => false]),
                    'consented_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        // Example GDPR request entries
        foreach ($users->take(3) as $u) {
            DB::table('gdpr_requests')->updateOrInsert(
                ['user_id' => $u->id, 'type' => 'export', 'status' => 'completed'],
                ['created_at' => now(), 'updated_at' => now(), 'notes' => null]
            );
        }
    }
}
