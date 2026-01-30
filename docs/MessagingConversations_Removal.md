# Removal: Messaging Conversations Feature

## Scope
- Remove all routes under /messaging/conversations*
- Remove UI pages: Messaging/Conversation.vue, Index.vue, NewConversation.vue, UXPrototype.vue
- Remove MessagingLayout usage; migrate dependent pages to AuthenticatedLayout
- Remove sidebar link to Messaging
- Remove tests that depend on conversations creation

## Changes
- routes/web.php: replace messaging group with redirect to home and 410 for legacy paths
- resources/js/Pages/Messaging/*: deleted
- resources/js/Layouts/MessagingLayout.vue: deleted
- resources/js/Components/SidebarNav.vue: remove Messaging item
- Migrate affected pages (Documentation/Newsletter) to AuthenticatedLayout
- tests/Feature/MessagingCreateDirectTest.php: deleted

## Verification
- Frontend build passes (`npm run build`)
- Manual QA: navigating /messaging/* redirects to Home or shows 410
- Sidebar no longer shows Messaging
- No broken imports of MessagingLayout remain

## Notes
- Service layer and models remain intact for future archival/reporting needs; no public routes reference them.
