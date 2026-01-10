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
        Schema::create('bookmark_collection_shares', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('collection_id');
            $table->uuid('shared_with_user_id');
            $table->uuid('shared_by_user_id');
            $table->enum('permission', ['view', 'edit'])->default('view');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->foreign('collection_id')->references('id')->on('bookmark_collections')->onDelete('cascade');
            $table->foreign('shared_with_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('shared_by_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['collection_id', 'shared_with_user_id'], 'bcs_collection_shared_unique');
            $table->index('collection_id');
            $table->index('shared_with_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookmark_collection_shares');
    }
};
