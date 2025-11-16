<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escrow_ledgers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('service_order_id')->index()->constrained()->onDelete('cascade');
            $table->foreignUuid('user_id')->nullable()->index(); // actor
            $table->enum('type', ['fund', 'release', 'refund', 'fee'])->index();
            $table->decimal('amount', 12, 2);
            $table->integer('milestone_index')->nullable(); // which milestone this relates to
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escrow_ledgers');
    }
};


