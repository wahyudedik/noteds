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
        Schema::table('note_view_history', function (Blueprint $table) {
            // Traffic source tracking
            $table->string('traffic_source')->nullable()->after('user_agent'); // direct, search, social, referral
            $table->string('referrer_url')->nullable()->after('traffic_source');
            $table->string('utm_source')->nullable()->after('referrer_url');
            $table->string('utm_medium')->nullable()->after('utm_source');
            $table->string('utm_campaign')->nullable()->after('utm_medium');
            
            // Geographic tracking
            $table->string('country_code', 2)->nullable()->after('utm_campaign');
            $table->string('country_name')->nullable()->after('country_code');
            $table->string('city')->nullable()->after('country_name');
            $table->string('region')->nullable()->after('city');
            
            // Hour tracking (for peak hours analysis)
            $table->integer('hour')->nullable()->after('region'); // 0-23
            
            // Indexes for analytics queries
            $table->index('traffic_source');
            $table->index('country_code');
            $table->index('hour');
            $table->index(['note_id', 'traffic_source']);
            $table->index(['note_id', 'country_code']);
            $table->index(['note_id', 'hour']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('note_view_history', function (Blueprint $table) {
            $table->dropIndex(['note_id', 'hour']);
            $table->dropIndex(['note_id', 'country_code']);
            $table->dropIndex(['note_id', 'traffic_source']);
            $table->dropIndex('hour');
            $table->dropIndex('country_code');
            $table->dropIndex('traffic_source');
            
            $table->dropColumn([
                'traffic_source',
                'referrer_url',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'country_code',
                'country_name',
                'city',
                'region',
                'hour',
            ]);
        });
    }
};
