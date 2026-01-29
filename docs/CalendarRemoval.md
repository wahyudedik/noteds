# Calendar Feature Removal

## Summary
- All calendar pages and APIs removed
- Event models/tables dropped with backup
- Group calendar endpoints disabled (410)
- Admin and user-facing navigation updated

## Removed/Disabled
- UI: resources/js/Pages/Calendar/CalendarView.vue
- Scheduling calendar UI: resources/js/Pages/Scheduling/ScheduleCalendar.vue
- Controller: app/Http/Controllers/CalendarController.php
- Job: app/Jobs/GenerateCalendarPdf.php
- View: resources/views/calendar/export.blade.php
- Models: app/Models/Event.php, EventInvitation.php, EventReminder.php
- Routes (web.php):
  - /calendar, /api/calendar/*
  - /api/scheduling/calendar
  - /events/calendar
  - /groups/{slug}/events/calendar
- Sidebar menu item “Calendar” removed

## Database
- Backup then drop tables (migration 2026_01_30_002000_backup_and_drop_calendar_tables.php):
  - events, event_invitations, event_reminders, event_categories
- Backups created as {table}_backup

## Tests
- Added RemoveCalendarFeatureTest to assert 410 on calendar endpoints

## Staging Checklist
- php artisan migrate --force
- php artisan optimize:clear
- Run tests:
  - php artisan test tests/Feature/RemoveCalendarFeatureTest.php
- Manual verify:
  - /calendar → 410
  - /api/calendar/* → 410
  - /events/calendar → 410
  - /groups/{slug}/events/calendar → 410
- Ensure no calendar items in sidebar
