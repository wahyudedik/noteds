<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('streaming_providers')) {
            Schema::create('streaming_providers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->enum('type', ['custom_hls','aws_ivs','livepeer']);
                $table->json('config')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        Schema::table('live_streams', function (Blueprint $table) {
            if (!Schema::hasColumn('live_streams', 'streaming_provider_id')) {
                $table->foreignId('streaming_provider_id')->nullable()->constrained('streaming_providers')->nullOnDelete();
            }
            if (!Schema::hasColumn('live_streams', 'group_id')) {
                $table->foreignUuid('group_id')->nullable()->constrained('groups')->nullOnDelete();
            }
            if (!Schema::hasColumn('live_streams', 'group_only')) {
                $table->boolean('group_only')->default(false);
            }
        });

        if (!Schema::hasTable('stream_logs')) {
            Schema::create('stream_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('live_stream_id')->nullable()->constrained('live_streams')->nullOnDelete();
                $table->string('provider')->nullable();
                $table->string('level')->default('info');
                $table->string('message');
                $table->json('context')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stream_logs');
        Schema::table('live_streams', function (Blueprint $table) {
            if (Schema::hasColumn('live_streams', 'streaming_provider_id')) {
                $table->dropForeign(['streaming_provider_id']);
                $table->dropColumn('streaming_provider_id');
            }
            if (Schema::hasColumn('live_streams', 'group_id')) {
                $table->dropForeign(['group_id']);
                $table->dropColumn('group_id');
            }
            if (Schema::hasColumn('live_streams', 'group_only')) {
                $table->dropColumn('group_only');
            }
        });
        Schema::dropIfExists('streaming_providers');
    }
};
