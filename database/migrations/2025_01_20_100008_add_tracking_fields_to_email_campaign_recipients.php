<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_campaign_recipients', function (Blueprint $table) {
            $table->string('tracking_token')->unique()->nullable()->after('id');
            $table->integer('open_count')->default(0)->after('opened_at');
            $table->integer('click_count')->default(0)->after('clicked_at');
            $table->json('clicked_links')->nullable()->after('click_count'); // Track which links were clicked
            $table->uuid('ab_test_id')->nullable()->after('sequence_id');
            $table->uuid('ab_variant_id')->nullable()->after('ab_test_id');
        });
    }

    public function down(): void
    {
        Schema::table('email_campaign_recipients', function (Blueprint $table) {
            $table->dropColumn(['tracking_token', 'open_count', 'click_count', 'clicked_links', 'ab_test_id', 'ab_variant_id']);
        });
    }
};

