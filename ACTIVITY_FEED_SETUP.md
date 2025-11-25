# Activity Feed Setup Guide

## ✅ Completed Steps

### 1. Database Migrations
All migrations have been successfully run:
- `activity_likes` table
- `activity_comments` table  
- `activity_shares` table

### 2. Routes
All routes are registered and working:
- `GET /activity` - Main activity feed
- `GET /activity/following` - Following users activity feed
- `GET /activity/{activity}` - Get activity details (AJAX)
- `POST /activity/{activity}/like` - Like/unlike activity
- `POST /activity/{activity}/comment` - Comment on activity
- `POST /activity/{activity}/share` - Share activity

### 3. Broadcasting Channels
Channels are configured in `routes/channels.php`:
- `activity-feed` - Public channel for new activities
- `activity.{activityId}` - Public channel for specific activity updates

## 📋 Current Status

### ✅ Working Features
- Activity feed display with pagination
- Filter by activity type
- Like/unlike activities
- Comment on activities (with nested replies)
- Share activities (Facebook, Twitter, LinkedIn, Copy Link)
- Activity feed for followed users
- Real-time event broadcasting (events are created and ready)

### ⚠️ Real-Time Updates (Optional Setup)

**Current Status:** Events are being broadcast, but frontend real-time updates require additional setup.

**For Development:**
- Broadcasting uses `log` driver by default
- Events are logged but not pushed to frontend in real-time
- Page refresh will show updates

**For Production (Real-Time Updates):**

1. **Install Laravel Echo & Pusher Client:**
   ```bash
   npm install --save-dev laravel-echo pusher-js
   ```

2. **Configure Broadcasting in `.env`:**
   ```env
   BROADCAST_DRIVER=pusher
   PUSHER_APP_ID=your-app-id
   PUSHER_APP_KEY=your-app-key
   PUSHER_APP_SECRET=your-app-secret
   PUSHER_APP_CLUSTER=your-cluster
   ```

3. **Setup Laravel Echo in `resources/js/bootstrap.js`:**
   ```javascript
   import Echo from 'laravel-echo';
   import Pusher from 'pusher-js';
   
   window.Pusher = Pusher;
   
   window.Echo = new Echo({
       broadcaster: 'pusher',
       key: import.meta.env.VITE_PUSHER_APP_KEY,
       cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
       forceTLS: true
   });
   ```

4. **Add to `resources/js/app.js`:**
   ```javascript
   import './bootstrap';
   ```

5. **Add to `.env`:**
   ```env
   VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
   VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
   ```

**Alternative: Redis + Socket.IO**
- Use `redis` as broadcast driver
- Install Socket.IO server
- Configure Laravel Echo with Socket.IO client

## 🎯 Usage

### Access Activity Feed
- Main feed: `/activity`
- Following feed: `/activity/following`

### Activity Types Supported
- `note.created` - When a note is created
- `note.purchased` - When a note is purchased
- `review.created` - When a review is created
- `user.followed` - When a user follows another user
- `bundle.purchased` - When a bundle is purchased
- `gift.sent` - When a gift is sent
- `gift.claimed` - When a gift is claimed

### API Endpoints

**Like Activity:**
```javascript
POST /activity/{activity}/like
Response: { liked: true/false, likes_count: 5 }
```

**Comment on Activity:**
```javascript
POST /activity/{activity}/comment
Body: { content: "Comment text", parent_id: null }
Response: { comment: {...}, comments_count: 3 }
```

**Share Activity:**
```javascript
POST /activity/{activity}/share
Body: { platform: "facebook|twitter|linkedin|copy_link" }
Response: { share: {...}, share_url: "...", shares_count: 2 }
```

## 📝 Notes

- Real-time updates are optional - the feed works perfectly without them
- All features (like, comment, share) work via AJAX without real-time
- Real-time is only for instant updates without page refresh
- For development, you can test all features without Pusher setup
- Events are still being logged and can be viewed in Laravel logs

## 🔧 Troubleshooting

**Routes not working?**
- Run: `php artisan route:clear`
- Run: `php artisan route:cache` (production only)

**Events not broadcasting?**
- Check `config/broadcasting.php` exists
- Verify `BROADCAST_DRIVER` in `.env`
- Check Laravel logs for broadcast events

**Real-time not working?**
- Verify Pusher credentials in `.env`
- Check browser console for Echo connection errors
- Ensure Laravel Echo is properly imported in `bootstrap.js`
- Verify channels are registered in `routes/channels.php`

