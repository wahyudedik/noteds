<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Generate usernames for existing users
        $users = User::whereNull('username')->get();
        foreach ($users as $user) {
            $baseUsername = Str::slug($user->name);
            $username = $baseUsername;
            $counter = 1;
            
            // Ensure uniqueness
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . '-' . $counter;
                $counter++;
            }
            
            $user->username = $username;
            $user->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to reverse
    }
};
