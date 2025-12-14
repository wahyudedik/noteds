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
        // Event challenges for gamification
        Schema::create('event_challenges', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->json('requirements'); // What users need to do
            $table->json('rewards'); // Points, cash, badges
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->boolean('active')->default(true);
            $table->integer('max_participants')->nullable();
            $table->timestamps();

            $table->index(['active', 'start_date', 'end_date']);
        });

        // Challenge participants tracking
        Schema::create('challenge_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained('event_challenges')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->integer('progress')->default(0);
            $table->boolean('completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['challenge_id', 'user_id']);
            $table->index(['user_id', 'completed']);
        });

        // Referral bonuses tracking
        Schema::create('referral_bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('referrer_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('referee_id')->constrained('users')->onDelete('cascade');
            $table->decimal('referrer_bonus', 15, 2);
            $table->decimal('referee_bonus', 15, 2);
            $table->decimal('multiplier', 5, 2)->default(1.00);
            $table->string('currency', 3)->default('IDR');
            $table->boolean('paid')->default(false);
            $table->timestamps();

            $table->index(['referrer_id', 'paid']);
            $table->index(['referee_id', 'created_at']);
        });

        // Streak rewards tracking
        Schema::create('streak_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->integer('streak_days');
            $table->integer('points_awarded')->default(0);
            $table->decimal('cash_awarded', 15, 2)->default(0);
            $table->string('badge_awarded')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'streak_days']);
            $table->index(['user_id', 'created_at']);
        });

        // Influencer conversions tracking
        Schema::create('influencer_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('influencer_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->string('code'); // Influencer tracking code
            $table->decimal('bonus_amount', 15, 2)->default(0);
            $table->boolean('bonus_paid')->default(false);
            $table->timestamps();

            $table->index(['influencer_id', 'bonus_paid']);
            $table->index(['code', 'created_at']);
        });

        // Add current_streak and last_activity_date to users table
        Schema::table('users', function (Blueprint $table) {
            $table->integer('current_streak')->default(0)->after('email');
            $table->date('last_activity_date')->nullable()->after('current_streak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['current_streak', 'last_activity_date']);
        });

        Schema::dropIfExists('influencer_conversions');
        Schema::dropIfExists('streak_rewards');
        Schema::dropIfExists('referral_bonuses');
        Schema::dropIfExists('challenge_participants');
        Schema::dropIfExists('event_challenges');
    }
};
