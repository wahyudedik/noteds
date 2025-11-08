# Notification Coverage Overview

This document tracks the current in-app notification coverage after the latest implementation pass. 

## Implemented

- **Wallet**
  - Top-up success (webhook or finish route) with updated balance
  - Top-up failed/expired/denied status
  - Withdraw request confirmation (pending)
  - Withdraw approval / rejection with admin notes
  - Low-balance alerts when wallet dips below configured threshold
- **Sales & Earnings**
  - Buyer purchase confirmation (links to purchased note)
  - Seller sale alert with buyer name and amount
  - Original creator commission when resale occurs (seller ≠ creator)
  - Note popularity milestones (10/25/50/100 purchases) for current owner
  - Seller daily digest summarising sales + performance nudges
- **Catalog**
  - New public note published (seller + followers)
- **Subscriptions**
  - Auto-renewal success with wallet balance check
  - Insufficient balance expiration notices
  - Pre-renewal reminder when balance is below required amount
- **Referrals**
  - Referral signup bonus credited
  - Referral buyer first-purchase bonus credited
- **Workspace**
  - Daily workspace digest (notes added, members joined, invitations sent)
- **Conversations & Reviews**
  - Buyer/seller DM (existing)
  - Review replies (existing)
  - Forum interactions (existing prior work)
- **AI & Recommendations**
  - AI analysis / Q&A / study materials / compare / extraction completion alerts
  - Product recommendation alerts when new suggestions are computed
- **Featured Notes**
  - Expiry reminder 1 day before placement ends
- **Admin & Ops**
  - High-value withdraw alerts to admins (configurable threshold)
- **Channels**
  - Push notification stub via `services.push.enabled` (logs until provider connected)

## Pending / Nice to Have

These are ideas discussed but not yet implemented. Use this list to prioritise next iterations.

- Queue-based AI completion notifications for long-running jobs (once async pipeline is added)
- Workspace task/comment digests (pending task feature)
- Weekly marketplace digest (wishlist activity, discovery insights) for sellers & buyers
- Automated follow-up reminders for scheduled promotions / featuring suggestions
- Full push provider integration (OneSignal/Firebase) replacing current stub logging

## Configuration Notes

- Wallet low-balance threshold is stored in settings (`wallet_low_balance_threshold`, default `50.000`). Update via settings seeder or admin panel once exposed.
- Subscription renewal reminders fire when `expired_at` is tomorrow **and** wallet balance is below the premium price. Adjust `subscriptions:renew` schedule so the command runs daily.
- Note popularity milestones rely on purchase count thresholds: `10, 25, 50, 100`. Edit `NotificationService::POPULARITY_THRESHOLDS` to tweak.
- Followers receive “new note” alerts only for public + active notes; metadata `notification_meta` on `notes` tracks last publish notification timestamp and milestones.
- Featured note reminders look 1 day ahead and set `reminder_sent_at` to avoid duplicates (`featured:expiry-reminders` schedule at 09:00 Asia/Jakarta).
- High-value withdraw threshold comes from `withdraw_high_value_threshold` setting (default `1_000_000`); admins with Spatie `admin` role receive alerts.
- Workspace digest runs at 07:00 Asia/Jakarta (`workspace:digest`) and aggregates `workspace_activity_logs` created within the last 24h.
- Marketplace daily digest runs at 08:00 Asia/Jakarta (`marketplace:daily-digest`) and uses successful transactions in the previous 24h.

## Testing Checklist (Manual)

- [x] Top-up success & failure (via webhook or payment finish) and low-balance follow-up.
- [x] Withdraw request (buyer) and approve/reject (admin) flows.
- [x] Subscription renewal run with both sufficient and insufficient balance cases.
- [x] Purchase pipeline: buyer notice, seller sale alert, original creator commission if resold, seller daily digest, note popularity milestone increments.
- [x] Referral signup + first purchase events.
- [x] Publish a new note and update an existing note to confirm publish notifications + follower alerts respect metadata.
- [x] Verify `notification_meta` persists milestones and publish timestamps across repeated events.
- [x] Trigger AI actions (analysis, Q&A, study materials, compare, extraction, recommendations) to confirm completion notifications.
- [x] Workspace note/member/invite events captured and included in the next digest run.
- [x] Featured note expiring within 24 hours sends reminder once.
- [x] High-value withdraw request (> threshold) alerts admins.
- [ ] Push stub logging occurs when `services.push.enabled=true` (smoke test). 

Feel free to append new ideas or move items between sections as work progresses.

