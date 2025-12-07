# Leaderboard Notification System

## Overview
The leaderboard reward distribution system now includes comprehensive notifications for both users and admins.

## Notification Types

### User Notifications

#### 1. **Leaderboard Reward Confirmation** 🎉
**Type:** `leaderboard_reward`
- **Trigger:** When a user receives a monthly leaderboard reward
- **Title:** "🎉 Selamat! Anda Mendapat Reward Leaderboard"
- **Content:** 
  - User's rank (#1-50)
  - Wallet amount received
  - Month of the reward
- **Action Link:** `/wallet` (to view wallet balance)
- **Data:**
  ```json
  {
    "rank": 1,
    "amount": 100000,
    "month": "2025-12",
    "action_url": "/wallet"
  }
  ```

### Admin Notifications

#### 1. **Reward Distribution Summary** ✅
**Type:** `admin_reward_distribution`
- **Trigger:** When all monthly rewards are successfully distributed
- **Title:** "✅ Leaderboard Rewards Distributed"
- **Content:** 
  - Number of users who received rewards
  - Total amount distributed
  - Month processed
  - Distribution timestamp
- **Sent To:** All admin users
- **Data:**
  ```json
  {
    "month": "2025-12",
    "recipient_count": 50,
    "total_amount": 500000,
    "distributed_at": "2025-12-08T10:30:00Z"
  }
  ```

#### 2. **Top Achiever Alert** 🥇
**Type:** `admin_top_achiever`
- **Trigger:** When Rank #1 reward is distributed
- **Title:** "🥇 Top Sharer: [User Name]"
- **Content:**
  - Top sharer's name and username
  - Month they topped the leaderboard
  - Wallet amount credited
- **Action Link:** User detail page at `/admin/users/{user_id}`
- **Priority:** HIGH - Highlighted for admin attention
- **Data:**
  ```json
  {
    "rank": 1,
    "user_id": 123,
    "user_name": "John Doe",
    "username": "johndoe",
    "amount": 100000,
    "month": "2025-12",
    "action_url": "/admin/users/123"
  }
  ```

#### 3. **Detailed Distribution Report** 📊
**Type:** `admin_reward_details`
- **Trigger:** When monthly rewards are distributed
- **Title:** "Detail Distribusi Reward - [Month]"
- **Content:** Complete list of all recipients
  ```
  Rank #1: John Doe (@johndoe) - Rp 100,000
  Rank #2: Jane Smith (@janesmith) - Rp 50,000
  Rank #3: Bob Johnson (@bobjohnson) - Rp 25,000
  ...
  Rank #50: Alice Brown (@alicebrown) - Rp 5,000
  ```
- **Data:**
  ```json
  {
    "month": "2025-12",
    "distribution_count": 50,
    "rewards": {...}
  }
  ```

## Notification Flow

```
End of Month (Auto-trigger via Scheduler)
    ↓
DistributeLeaderboardRewardsJob runs
    ↓
For each user in top 50:
    ├─ Add wallet balance
    ├─ Create MonthlyShareReward record
    └─ Send AppNotification
    ↓
For each admin user:
    ├─ Summary notification (✅ distributed)
    ├─ Top achiever alert (🥇 rank 1)
    └─ Detailed list (📊 all rewards)
```

## Configuration

All notifications respect the following settings from admin panel:

| Setting | Value | Location |
|---------|-------|----------|
| `auto_transfer_rewards` | true/false | Admin → Leaderboard Settings → Auto Transfer Rewards |
| `monthly_reward_rank_1` | 100000 | Admin → Leaderboard Settings → Rank 1 Reward |
| `monthly_reward_rank_2` | 50000 | Admin → Leaderboard Settings → Rank 2 Reward |
| `monthly_reward_rank_3` | 25000 | Admin → Leaderboard Settings → Rank 3 Reward |
| `monthly_reward_top_10` | 10000 | Admin → Leaderboard Settings → Top 4-10 Reward |
| `monthly_reward_top_50` | 5000 | Admin → Leaderboard Settings → Top 11-50 Reward |
| `reward_transfer_day` | 1-31 | Admin → Leaderboard Settings → Transfer Day |

## Implementation Details

### Job Class
- **File:** `app/Jobs/DistributeLeaderboardRewardsJob.php`
- **Schedule:** Runs automatically at configured `reward_transfer_day` each month
- **Queue:** Uses Laravel's default queue system

### Model
- **Notification Model:** `App\Models\AppNotification`
- **Reward Record Model:** `App\Models\MonthlyShareReward`
- **Wallet Model:** `App\Models\Wallet`

### Database Tables

**notifications** (via AppNotification)
```sql
- id (uuid)
- user_id (foreign key)
- type (string) - leaderboard_reward, admin_reward_distribution, etc.
- title (string)
- message (text)
- data (json) - contains rank, amount, month, action_url
- read_at (timestamp, nullable)
- created_at, updated_at
```

## Testing the Notification System

### Manual Test (without scheduler):
```bash
# Run the distribution job manually for a specific month
php artisan queue:work

# In your code or tinker:
dispatch(new \App\Jobs\DistributeLeaderboardRewardsJob('2025-12'));
```

### View Notifications:
1. **User Notifications:** Navigate to `/notifications` after reward distribution
2. **Admin Notifications:** Dashboard → Notifications panel
3. **Database Query:**
   ```sql
   SELECT * FROM notifications 
   WHERE type LIKE 'leaderboard_%' 
   OR type LIKE 'admin_reward_%'
   ORDER BY created_at DESC;
   ```

## Features

✅ **User-Friendly Messages:** Celebration emoji (🎉) for users, important alerts (✅🥇📊) for admins  
✅ **Contextual Links:** Quick access to wallet, user profiles, and admin panels  
✅ **JSON Data:** Structured data for integrations and custom handling  
✅ **Admin Oversight:** Detailed reports for admin accountability  
✅ **Audit Trail:** All distributions logged and notified  
✅ **Configurable Rewards:** All reward amounts configurable in admin panel  

## Future Enhancements

- [ ] Email notifications (in addition to in-app)
- [ ] SMS notifications for top 3 achievers
- [ ] Notification preferences (user can opt-in/out)
- [ ] Bulk notification management for admins
- [ ] Notification history/archive
- [ ] Export distribution reports as PDF/CSV

