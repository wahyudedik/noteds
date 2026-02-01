# Plugins Pages Layout Consistency

## Overview
- Pages aligned with existing `AuthenticatedLayout` for consistent header, sidebar, content area, footer, and mobile behavior.
- Applies Tailwind utility classes used across the app for spacing, typography, borders and dark mode.

## Files Updated
- Admin list: `resources/js/Pages/Admin/Plugins/Index.vue`
- Admin detail: `resources/js/Pages/Admin/Plugins/Show.vue`
- User catalog: `resources/js/Pages/Plugins/Index.vue`

## Layout & Components
- Layout: `AuthenticatedLayout` providing:
  - SidebarNav (desktop/mobile drawer)
  - TopBar
  - BottomNav (mobile)
  - Header slot for page titles (`h2.text-xl.font-semibold`)
  - Content container (`px-4 py-6 lg:px-6`, `max-w-7xl`)
- Navigation: Sidebar updated with `Plugins` item linking to `/plugins`
- Admin Quick Actions: Added `Manage Plugins` button to dashboard

## CSS & UI Consistency
- Headings: `text-xl font-semibold` for page title, `text-lg font-semibold` for card titles
- Spacing: `px-4 py-6 lg:px-6`, internal `mb-2/mb-4/mb-6`
- Typography: `text-gray-800/900` (light), `dark:text-gray-200/white` (dark)
- Tables: `min-w-full`, `divide-y divide-gray-200`, `dark:divide-gray-700`
- Cards: `bg-white dark:bg-gray-800`, `rounded-lg`, `shadow-sm`, `border`
- Buttons: `.btn`, `.btn-primary`, `.btn-secondary`, `.btn-success`, `.btn-warning`, `.btn-sm`

## Responsiveness
- Grid: `grid-cols-1 md:grid-cols-2 lg:grid-cols-3` for plugin cards
- Sidebar collapses on mobile via `AuthenticatedLayout`
- Tables wrapped in `overflow-x-auto` for small screens

## Verification
- Visual parity with dashboard pages:
  - Header style identical
  - Container width and spacing match
  - Dark mode colors consistent
- Mobile:
  - Sidebar drawer works
  - Content padding and grids adapt

## Maintenance Notes
- Use `AuthenticatedLayout` for future plugins pages to inherit global UI
- Reuse existing Tailwind patterns for tables/cards/forms
- Document any new button variants in component styles to stay consistent

