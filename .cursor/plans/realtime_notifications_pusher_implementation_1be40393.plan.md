---
name: realtime_notifications_pusher_implementation
overview: Implement full real-time notifications in the Noteds Laravel app using Pusher and Laravel Broadcasting, including live updates, notification center, sounds, preferences, and history.
todos:
  - id: setup-pusher-config
    content: Configure Laravel broadcasting with Pusher (env vars, `config/broadcasting.php`, private channels in `routes/channels.php`).
    status: completed
  - id: implement-backend-notifications
    content: Create notification classes, events, and history endpoints (controller + routes) aligned with Noteds domain actions.
    status: completed
    dependencies:
      - setup-pusher-config
  - id: frontend-echo-and-ui
    content: Initialize Echo/Pusher on the frontend, subscribe to private channels, and build notification bell + center UI with real-time updates and sound.
    status: completed
    dependencies:
      - setup-pusher-config
      - implement-backend-notifications
  - id: preferences-and-settings
    content: Design and implement notification preferences (DB + settings UI) and enforce them when sending notifications.
    status: completed
    dependencies:
      - implement-backend-notifications
  - id: testing-and-hardening
    content: Add tests, optimize queries, and verify security, performance, and UX polish for the notification experience.
    status: completed
    dependencies:
      - frontend-echo-and-ui
      - preferences-and-settings
---

# Real-Time Notifications with Pusher – Implementation Plan

## 1. Backend broadcasting & Pusher configuration

- **Ensure dependencies**
- Confirm Laravel version and that `pusher/pusher-php-server` is installed; if not, add it via Composer.
- **Environment & config setup**
- Set `PUSHER_APP_ID`, `PUSHER_APP_KEY`, `PUSHER_APP_SECRET`, `PUSHER_APP_CLUSTER` and `PUSHER_HOST`/`PUSHER_PORT` if needed in `.env`, and map them to `VITE_PUSHER_*` variables.
- Configure `config/broadcasting.php` to use the `pusher` driver as default and ensure correct options (cluster, TLS, etc.).
- Run `php artisan config:clear` after changes.
- **Broadcast routes & channels**
- In [`routes/channels.php`](routes/channels.php), define private channels for user notifications, e.g. `private-notifications.{userId}` with appropriate authorization callback.
- If there are group-related features, also define channels for groups/communities (e.g. `private-group.{groupId}`) for future extensibility.

## 2. Notification models, database & history

- **Database structure**
- Reuse Laravel’s built-in `notifications` table if using `Notifiable`, or add any missing columns needed by the UI (e.g. `type`, `data`, `read_at`).
- If more complex preferences/history are needed, create additional tables such as `notification_preferences` and possibly a `notification_types` reference table.
- **Laravel Notification classes**
- Create dedicated notification classes (e.g. `NewCommentNotification`, `NewMentionNotification`, `NewGroupPostNotification`) under `app/Notifications` that implement `toDatabase` and `toBroadcast` methods.
- Ensure these notifications implement `ShouldBroadcast` so that they emit broadcast events onto the appropriate Pusher channels.
- **History retrieval**
- Implement a `NotificationController` (e.g. [`app/Http/Controllers/NotificationController.php`](app/Http/Controllers/NotificationController.php)) with endpoints for:
    - Listing paginated notification history for the current user (filter by `read/unread`, type, date range).
    - Marking a single notification as read.
    - Marking all notifications as read.
- Add routes in [`routes/web.php`](routes/web.php) or [`routes/api.php`](routes/api.php) depending on SPA vs traditional layout.

## 3. Real-time broadcasting events & triggers

- **Broadcast events**
- If needed, create explicit broadcast events (e.g. `NotificationCreated`) in `app/Events` that wrap the notification payload and broadcast on `private-notifications.{userId}`.
- Define `broadcastOn`, `broadcastAs`, and payload structure to match what the frontend expects (e.g. `id`, `title`, `message`, `url`, `created_at`, `read_at`, `type`, `icon`).
- **Trigger points in domain logic**
- Identify key actions in Noteds where notifications should be generated (e.g. new comment, new reply, mention, group invite, task assignment) and ensure they call the appropriate Notification classes.
- Centralize this logic in service classes (e.g. `NotificationService`) where possible to avoid duplication and ease future extensions.

## 4. Frontend WebSocket / Echo initialization (fixing the “app key” error)

- **Vite environment access**
- In [`resources/js/bootstrap.js`](resources/js/bootstrap.js) (or equivalent entry file), configure Laravel Echo with Pusher:
    - Import `pusher-js`, set `window.Pusher = Pusher`.
    - Instantiate `window.Echo` using `import.meta.env.VITE_PUSHER_APP_KEY`, `VITE_PUSHER_APP_CLUSTER`, and TLS options.
- Ensure that the `VITE_PUSHER_APP_KEY` is correctly compiled by running the frontend build (`npm run dev` or `npm run build`).
- **Channel subscriptions**
- On the authenticated area of the app (e.g. layout JS), subscribe to the private notification channel `private-notifications.{userId}` via Echo, and register a listener for the notification event name (e.g. `.NotificationCreated`).
- On receiving an event, update the in-memory notification state (e.g. Vue/React/Alpine store or simple global JS state) and trigger UI updates (badge count, dropdown list, sound).

## 5. Notification center UI (web)

- **Notification icon & dropdown**
- In the main navbar layout (e.g. [`resources/views/layouts/app.blade.php`](resources/views/layouts/app.blade.php) or equivalent JS component), add a notification bell icon with:
    - Real-time badge count showing the number of unread notifications.
    - Dropdown / panel listing recent notifications with infinite scroll or “View all” link.
- Each item should display at least: title, short message, type icon, created time (e.g. `5 minutes ago`), read/unread style, and a link to the related resource.
- **Full notification center page**
- Create a full page (e.g. [`resources/views/notifications/index.blade.php`](resources/views/notifications/index.blade.php) or a SPA route) that shows the full history with filters and pagination.
- Integrate actions:
    - Mark individual notification as read/unread (AJAX/SPA call to controller).
    - Mark all as read.
    - Optional bulk actions (e.g. delete or archive if desired).

## 6. Sound alerts & UX details

- **Sound playback**
- Add a short notification sound asset under `public/sounds/notification.mp3` (or similar).
- On receiving a new unread notification in the Echo listener, check user preference (see next section) and if enabled, play the sound with the Web Audio API or a simple HTML audio element.
- **Visual cues**
- Add small animation (e.g. pulse on the bell icon) when a new notification arrives.
- Make sure animations and sounds respect accessibility (e.g. reduced motion, mute option).

## 7. Notification preferences per type

- **Data model**
- Create a `notification_preferences` table linking to `users` with columns like: `type`, `via_database`, `via_email`, `via_push`, `sound_enabled`, `desktop_enabled`.
- Seed default preferences for new users (e.g. all database notifications enabled, sounds enabled only for high-priority types).
- **Preference management UI**
- Add a Notifications section in the user settings page (e.g. [`resources/views/settings/notifications.blade.php`](resources/views/settings/notifications.blade.php)):
    - List all notification types with toggles for: “Show in notification center”, “Play sound”, and any other channels planned.
- Implement controller methods and routes to update preferences (validating that types are known and owned by the current user).
- **Enforcement in backend**
- Before sending a notification, check the user’s preferences for that type to decide whether to create a database/broadcast notification and whether to include sound/priority flags in the payload.

## 8. Push notifications (browser, and groundwork for mobile)

- **Browser push (phase 1)**
- Decide whether to use the browser’s native Push API or a service like Firebase Cloud Messaging (FCM) in a later phase.
- For this iteration, prepare the data model: add flags on notifications indicating `can_push` and `push_title/body/url`, so later a worker/service can send push messages.
- **Mobile push groundwork (phase 2)**
- Define a generic interface (e.g. `PushNotificationService`) in the backend that can be implemented later for mobile apps, re-using the same notification payloads and preferences.

## 9. Security, performance, and testing

- **Authorization & privacy**
- Verify that private channels only deliver notifications to their owners (auth callbacks in `routes/channels.php`).
- For group/community channels, ensure only authorized members can subscribe.
- **Performance considerations**
- Use pagination and lazy-loading in notification lists.
- Index `notifications` table on `notifiable_id`, `notifiable_type`, `read_at`, and `created_at`.
- Consider pruning or archiving old notifications via a scheduled command (e.g. `php artisan schedule:work` with a `PruneOldNotifications` job).
- **Testing**
- Write feature tests for notification creation, history retrieval, and mark-as-read endpoints.
- Manually test Pusher connections using the Pusher debug console to ensure events are delivered.
- Validate that the JavaScript no longer logs `You must pass your app key when you instantiate Pusher` and that events appear live in the UI.

## 10. Integration into the existing Noteds feature roadmap

- **Align with FEATURE_GAP_ANALYSIS.md**
- Map implemented capabilities back to the requirements at lines 114–128: WebSocket live updates, push hooks, notification center, sound alerts, per-type preferences, mark-all-as-read, and history.
- Update `FEATURE_GAP_ANALYSIS.md` to mark real-time notifications as implemented/partially implemented with remaining work clearly documented.
- **Future enhancements**
- Add notification categories with separate tabs (e.g. "Tasks", "Comments", "Mentions").
- Add desktop notifications (via `Notification` API) respecting user preferences.