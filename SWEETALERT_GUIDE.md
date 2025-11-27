# SweetAlert2 Utilities - Usage Guide

## Overview

SweetAlert2 utilities provide a clean, reusable interface for showing alerts, toasts, and confirmations throughout the application.

## Available Functions

### Toast Notifications (Non-blocking)

```javascript
// Show generic toast
showToast(icon, message, options);

// Specific toast shortcuts
showSuccess(message, options);
showError(message, options);
showWarning(message, options);
showInfo(message, options);
```

**Examples:**

```javascript
// Simple success toast
showSuccess('Note created successfully!');

// With custom options
showSuccess('Settings saved!', { 
    timer: 5000,
    position: 'top-center'
});

// Toast with custom HTML
showInfo('Processing...', {
    html: 'Please wait while we process your request',
    timer: false
});
```

### Confirmation Dialogs (Blocking)

```javascript
// Generic confirmation
showConfirm(config).then((result) => {
    if (result.isConfirmed) {
        // User clicked confirm
    }
});

// Delete confirmation (predefined style)
showDeleteConfirm(config).then((result) => {
    if (result.isConfirmed) {
        // Perform delete action
    }
});

// Custom alert
showAlert(config).then((result) => {
    // Handle result
});
```

**Examples:**

```javascript
// Simple confirmation
showConfirm({
    title: 'Are you sure?',
    confirmButtonText: 'Yes, proceed'
}).then((result) => {
    if (result.isConfirmed) {
        console.log('User confirmed');
    }
});

// Delete confirmation
showDeleteConfirm({
    title: 'Delete this note?',
    html: 'This action will permanently delete the note and cannot be undone.'
}).then((result) => {
    if (result.isConfirmed) {
        // Submit delete form or fetch
    }
});

// Form submission confirmation
showConfirm({
    title: 'Submit form?',
    html: 'Please review your changes before submitting.',
    confirmButtonText: 'Yes, submit'
}).then((result) => {
    if (result.isConfirmed) {
        form.submit();
    }
});
```

### Loading Dialogs

```javascript
// Show loading
showLoading('Processing your request...');

// Hide loading
hideLoading();

// Close dialog
closeAlert();
```

**Example:**

```javascript
// Start loading
showLoading('Uploading file...');

// Simulate async operation
setTimeout(() => {
    hideLoading();
    showSuccess('File uploaded successfully!');
}, 2000);
```

## HTML Data Attributes (Declarative Alerts)

### Delete Confirmation via HTML

```html
<!-- Simple delete button with confirmation -->
<button 
    data-confirm-delete 
    data-delete-url="/notes/{{ $note->id }}"
    data-item-name="note"
    class="btn btn-danger">
    Delete
</button>

<!-- With custom message -->
<button 
    data-confirm-delete 
    data-delete-url="/users/{{ $user->id }}"
    data-item-name="user"
    data-delete-message="This user and all their data will be permanently deleted."
    class="btn btn-danger">
    Delete User
</button>
```

### Form Submission Confirmation

```html
<form id="settings-form" method="POST" action="/settings">
    @csrf
    <!-- form fields -->
    
    <button 
        type="button"
        data-confirm-submit 
        data-confirm-message="Save these settings?"
        class="btn btn-primary">
        Save Settings
    </button>
</form>
```

## Flash Messages (Server-side)

Flash messages from Laravel controller are automatically displayed as toasts:

```php
// Controller
return redirect('/dashboard')
    ->with('success', 'Note created successfully!');

// Automatically shows as SweetAlert toast
```

**Flash message types:**
- `success` - Green toast
- `error` - Red toast
- `warning` - Yellow toast
- `info` - Blue toast

## Programmatic Examples

### Delete with API Call

```javascript
document.querySelector('.delete-btn').addEventListener('click', () => {
    showDeleteConfirm({
        title: 'Delete this item?'
    }).then(async (result) => {
        if (result.isConfirmed) {
            showLoading('Deleting...');
            try {
                const response = await fetch('/api/items/123', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
                    }
                });
                if (response.ok) {
                    hideLoading();
                    showSuccess('Item deleted!');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    hideLoading();
                    showError('Failed to delete item');
                }
            } catch (error) {
                hideLoading();
                showError('Error: ' + error.message);
            }
        }
    });
});
```

### Form Submission with Validation

```javascript
document.querySelector('#myForm').addEventListener('submit', (e) => {
    e.preventDefault();
    
    showConfirm({
        title: 'Submit form?',
        html: 'Please review your information before submitting.'
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading('Submitting form...');
            // Simulate validation/submission
            setTimeout(() => {
                // On success
                hideLoading();
                showSuccess('Form submitted!');
                e.target.submit();
            }, 1500);
        }
    });
});
```

### Chained Confirmations

```javascript
// Ask for confirmation, then load data
showConfirm({
    title: 'Continue to next step?'
}).then((result) => {
    if (result.isConfirmed) {
        showLoading('Loading...');
        fetch('/api/next-step')
            .then(r => r.json())
            .then(data => {
                hideLoading();
                showSuccess('Ready for next step!');
            });
    }
});
```

## Configuration Options

Each function accepts a config object with SweetAlert2 options:

```javascript
{
    // Core options
    title: 'Title',
    html: 'Custom HTML content',
    icon: 'success|error|warning|info|question',
    
    // Buttons
    showConfirmButton: true,
    confirmButtonText: 'OK',
    confirmButtonColor: '#3085d6',
    showCancelButton: true,
    cancelButtonText: 'Cancel',
    cancelButtonColor: '#d33',
    reverseButtons: false,
    
    // Behavior
    allowOutsideClick: false,
    allowEscapeKey: true,
    didOpen: (dialog) => { /* callback */ },
    willClose: (dialog) => { /* callback */ },
    
    // Timer (for toasts)
    timer: 3000,
    timerProgressBar: true,
    
    // Other
    backdrop: 'rgba(0,0,0,0.4)'
}
```

## Best Practices

1. **Use toasts for non-critical notifications** - Success messages, info, simple feedback
2. **Use dialogs for important confirmations** - Delete, submit, irreversible actions
3. **Always await confirmations** - Don't proceed without user confirmation
4. **Provide context** - Explain what will happen in the confirmation message
5. **Use data attributes** - For simple delete/submit confirmations in HTML
6. **Custom options** - Override defaults when needed (timer, text, colors)

## Backward Compatibility

The old `window.NotedsToast()` function still works:

```javascript
// Old way (still works)
window.NotedsToast('success', 'Message here');

// New way (recommended)
window.showSuccess('Message here');
```

---

For more SweetAlert2 options, see: https://sweetalert2.github.io/
