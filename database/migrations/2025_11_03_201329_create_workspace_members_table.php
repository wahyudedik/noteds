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
        Schema::create('workspace_members', function (Blueprint $table) {
            $table->uuid('workspace_id');
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('member'); // 'owner', 'admin', 'member'
            $table->boolean('is_active')->default(true);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->primary(['workspace_id', 'user_id']);
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->index(['workspace_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_members');
    }
};
