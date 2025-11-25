<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if workspace_members table exists
        if (!Schema::hasTable('workspace_members')) {
            return;
        }

        Schema::table('workspace_members', function (Blueprint $table) {
            $table->json('permissions')->nullable()->after('role'); // Custom permissions per member
            $table->string('team_role')->nullable()->after('permissions'); // Team-specific role (e.g., developer, designer, manager)
            $table->boolean('can_manage_members')->default(false)->after('team_role');
            $table->boolean('can_manage_workspace')->default(false)->after('can_manage_members');
            $table->boolean('can_create_notes')->default(true)->after('can_manage_workspace');
            $table->boolean('can_edit_notes')->default(true)->after('can_create_notes');
            $table->boolean('can_delete_notes')->default(false)->after('can_edit_notes');
            $table->boolean('can_manage_folders')->default(false)->after('can_delete_notes');
            $table->boolean('can_invite_members')->default(false)->after('can_manage_folders');
        });
    }

    public function down(): void
    {
        Schema::table('workspace_members', function (Blueprint $table) {
            $table->dropColumn([
                'permissions',
                'team_role',
                'can_manage_members',
                'can_manage_workspace',
                'can_create_notes',
                'can_edit_notes',
                'can_delete_notes',
                'can_manage_folders',
                'can_invite_members',
            ]);
        });
    }
};

