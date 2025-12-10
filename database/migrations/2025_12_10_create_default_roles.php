<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure default roles exist
        // This complements RoleSeeder but ensures roles are always available
        \Spatie\Permission\Models\Role::firstOrCreate(
            ['name' => 'admin'],
            ['guard_name' => 'web']
        );
        
        \Spatie\Permission\Models\Role::firstOrCreate(
            ['name' => 'seller'],
            ['guard_name' => 'web']
        );
        
        \Spatie\Permission\Models\Role::firstOrCreate(
            ['name' => 'buyer'],
            ['guard_name' => 'web']
        );
        
        \Spatie\Permission\Models\Role::firstOrCreate(
            ['name' => 'user_workspaces'],
            ['guard_name' => 'web']
        );
        
        \Spatie\Permission\Models\Role::firstOrCreate(
            ['name' => 'vendor'],
            ['guard_name' => 'web']
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep roles in database when rolling back
        // They're system-level data, not schema changes
    }
};
