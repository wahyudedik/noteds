<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckSellerRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-seller-role {email? : The email of the user to check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and fix seller user roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        if (!$email) {
            $this->info('Checking all users with seller-related roles/permissions...');

            // Show all users with seller role
            $sellers = User::where('role', 'seller')->get();
            $this->info("\n=== Users with role='seller' ===");
            if ($sellers->isEmpty()) {
                $this->warn("No users with role='seller' found");
            } else {
                foreach ($sellers as $user) {
                    $this->line("- {$user->name} ({$user->email}) - Role: {$user->role}");
                }
            }

            // Show users with spatie seller role
            $this->info("\n=== Users with Spatie 'seller' role ===");
            $spaatieSellers = User::role('seller')->get();
            if ($spaatieSellers->isEmpty()) {
                $this->warn("No users with Spatie 'seller' role found");
            } else {
                foreach ($spaatieSellers as $user) {
                    $this->line("- {$user->name} ({$user->email}) - Column Role: {$user->role}");
                }
            }

            return;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found");
            return;
        }

        $this->info("\n=== User Information ===");
        $this->line("Name: {$user->name}");
        $this->line("Email: {$user->email}");
        $this->line("Current role column: {$user->role}");
        $this->line("Has 'seller' Spatie role: " . ($user->hasRole('seller') ? 'Yes' : 'No'));
        $this->line("Has 'buyer' Spatie role: " . ($user->hasRole('buyer') ? 'Yes' : 'No'));
        $this->line("Has 'admin' Spatie role: " . ($user->hasRole('admin') ? 'Yes' : 'No'));

        $this->info("\nSpatie roles: " . ($user->getRoleNames()->isEmpty() ? 'None' : $user->getRoleNames()->implode(', ')));

        // Suggest action
        if ($user->role !== 'seller') {
            $this->warn("\n⚠️  This user's role column is NOT set to 'seller'");
            if ($this->confirm("Do you want to change it to 'seller'?")) {
                $user->update(['role' => 'seller']);
                $this->info("✅ User role updated to 'seller'");
            }
        }
    }
}
