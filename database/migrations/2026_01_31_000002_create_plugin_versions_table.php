<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plugin_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('plugin_id');
            $table->string('version');
            $table->json('manifest')->nullable();
            $table->string('archive_path')->nullable();
            $table->string('storage_path')->nullable();
            $table->string('checksum')->nullable();
            $table->string('migration_status')->default('none'); // none|applied|failed|rolled_back
            $table->timestamp('installed_at')->nullable();
            $table->timestamps();

            $table->foreign('plugin_id')->references('id')->on('plugins')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_versions');
    }
};

