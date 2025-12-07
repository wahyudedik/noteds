# Leaderboard System - Implementation Plan

**Date:** December 7, 2025  
**Status:** Planning & Implementation

## Issues & Requirements

### 1. BUG FIXES
- [x] Fix username not displaying in leaderboard (eager loading issue in `getLeaderboard`)
- [ ] Implement duplicate share prevention (one product per user = 1 share only)

### 2. NEW FEATURES - Admin Settings

#### 2.1 Leaderboard Settings Model
- Table: `leaderboard_settings`
- Fields:
  - `id` (int, PK)
  - `setting_key` (string, unique)
  - `setting_value` (string/text)
  - `description` (string)
  - `type` (string: int, float, boolean, json)
  - `created_at`, `updated_at`

#### 2.2 Settings to Create

**Points System**
- `share_points_per_share` (default: 10)
- `share_points_per_click` (default: 5)
- `share_points_per_purchase` (default: 50)

**Monthly Leaderboard**
- `leaderboard_monthly_point_cap` (default: 10000)
- `leaderboard_monthly_point_target` (default: 10000) - min points to be eligible

**Monthly Rewards**
- `monthly_reward_rank_1` (default: 100000)
- `monthly_reward_rank_2` (default: 50000)
- `monthly_reward_rank_3` (default: 25000)
- `monthly_reward_top_10` (default: 10000)
- `monthly_reward_top_50` (default: 5000)

**Reward Distribution**
- `leaderboard_reward_enabled` (default: true)
- `leaderboard_auto_transfer` (default: true)
- `leaderboard_transfer_day` (default: 5) - which day of month to transfer

### 2.3 Admin UI
- New admin page: `/admin/settings/leaderboard`
- Settings tabs in existing admin settings
- Forms for all configurable values
- Preview of current rewards

### 2.4 Database Migrations
- Create `leaderboard_settings` table
- Create `share_point_limits` to track duplicate shares
- Add fields to `monthly_share_rewards` for status tracking

### 2.5 Controllers
- Create `LeaderboardSettingsController` (admin)
- Update `ShareToEarnService` to use configurable values
- Update `ShareLeaderboardController` to apply monthly cap

### 2.6 Services
- Update `ShareToEarnService`:
  - Add duplicate share prevention
  - Add monthly point cap logic
  - Add configurable point rewards
  - Add auto-transfer scheduling

### 2.7 Scheduling
- Create `DistributeLeaderboardRewardsJob`
- Schedule via `schedule:run` command
- Transfer from admin wallet to top 3 users

## Implementation Sequence

1. Fix username bug in `getLeaderboard`
2. Create migration for `leaderboard_settings` table
3. Create `LeaderboardSetting` model
4. Update `ShareToEarnService`:
   - Fix getLeaderboard eager loading
   - Add duplicate share prevention
   - Add monthly cap logic
   - Make all values configurable
5. Create admin controller & views for leaderboard settings
6. Add quick link button to admin dashboard
7. Create reward distribution job & scheduling
8. Test end-to-end

## Files to Create/Modify

**New:**
- `app/Models/LeaderboardSetting.php`
- `app/Http/Controllers/Admin/LeaderboardSettingsController.php`
- `database/migrations/create_leaderboard_settings_table.php`
- `database/migrations/add_fields_to_share_points_table.php`
- `app/Jobs/DistributeLeaderboardRewardsJob.php`
- `resources/views/admin/leaderboard-settings/index.blade.php`

**Modify:**
- `app/Services/ShareToEarnService.php`
- `app/Http/Controllers/ShareLeaderboardController.php`
- `app/Models/SharePoint.php`
- `routes/web.php`
- `resources/views/admin/dashboard.blade.php` (add quick link)
- `resources/views/share/leaderboard.blade.php` (fix username display)

## Timeline Estimate
- Fix username bug: 15 min
- Database setup: 30 min
- Services & logic: 1 hour
- Admin UI: 45 min
- Scheduling & jobs: 30 min
- Testing: 30 min

**Total: ~3.5 hours**
