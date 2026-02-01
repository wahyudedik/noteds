<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plugins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('version');
            $table->string('type')->default('web'); // web|android|hybrid
            $table->string('author')->nullable();
            $table->string('android_package_name')->nullable();
            $table->text('description')->nullable();
            $table->json('manifest')->nullable();
            $table->json('dependencies')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('enabled')->default(false);
            $table->string('checksum')->nullable();
            $table->string('storage_path')->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plugins');
    }
};

