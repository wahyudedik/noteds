# Toast Notifications

## Component
- Global container: [ToastContainer.vue](file:///d:/PROJECT/LARAVEL/noteds/resources/js/Components/Common/ToastContainer.vue), mounted in [MessagingLayout](file:///d:/PROJECT/LARAVEL/noteds/resources/js/Layouts/MessagingLayout.vue)
- API: window.__toast.add({ title, message, type, duration })

## Usage
- Call panel emits toasts on participant join/leave and permission changes
- Type: success | error | info
- Default position: top-right; duration default 3s

## Configuration
- Extend container to accept props for position/duration via layout integration
- Add CSS transitions for smooth animations
