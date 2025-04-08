# SweetAlert Notification System

This document provides guidelines for using the SweetAlert notification system in the Iansoft Ticket Management System.

## Overview

SweetAlert2 is integrated throughout the application to provide consistent, beautiful notifications that automatically fade after 3 seconds. This implementation ensures a unified user experience across all parts of the system.

## How It Works

1. The system uses CDN links to load SweetAlert2 instead of local npm assets
2. Flash messages are automatically converted to toast notifications
3. You can use either PHP helpers or direct JavaScript functions to show notifications

## Important Note for Redirects

When using SweetAlert notifications together with redirects, always use the promise-based approach instead of `setTimeout`. This ensures the notification is shown before navigating away from the page.

### ✅ Correct way (using promise):
```javascript
// Show alert and redirect only after alert is closed
showAlert('Success', 'Operation completed').then(() => {
    window.location.href = '/your/redirect/url';
});

// Or with Swal directly
Swal.fire({
    title: 'Success',
    text: 'Operation completed',
    icon: 'success',
    timer: 3000,
    timerProgressBar: true
}).then(() => {
    window.location.href = '/your/redirect/url';
});
```

### ❌ Incorrect way (using setTimeout):
```javascript
// DON'T DO THIS - redirect may happen before alert is visible
showAlert('Success', 'Operation completed');
setTimeout(() => {
    window.location.href = '/your/redirect/url';
}, 3000);
```

## Usage Options

### 1. PHP Controller (Backend)

Use the `NotificationHelper` class to set flash messages that will be automatically converted to SweetAlert toasts:

```php
use app\components\NotificationHelper;

// Success message
NotificationHelper::success('Operation completed successfully');

// Error message
NotificationHelper::error('An error occurred');

// Warning message
NotificationHelper::warning('Please review your input');

// Information message
NotificationHelper::info('Your session will expire in 10 minutes');

// Direct display (without using flash)
NotificationHelper::show('Immediate message', 'Title', 'success');
```

### 2. JavaScript (Frontend)

Use the global `showAlert` function to display notifications directly from JavaScript:

```javascript
// Simple success message
showAlert('Success', 'Operation completed successfully');

// Error message
showAlert('Error', 'An error occurred', 'error');

// Warning message
showAlert('Warning', 'Please review your input', 'warning');

// Information message
showAlert('Information', 'Your session will expire in 10 minutes', 'info');
```

### 3. AJAX Responses

For AJAX responses, use the following pattern to handle server responses and show notifications:

```javascript
$.ajax({
    url: '/your/endpoint',
    type: 'POST',
    data: data,
    success: function(response) {
        if (response.success) {
            // Use promise-based approach for redirects
            showAlert('Success', response.message).then(() => {
                window.location.href = '/your/redirect/url';
            });
        } else {
            showAlert('Error', response.message, 'error');
        }
    },
    error: function() {
        showAlert('Error', 'An error occurred', 'error');
    }
});
```

### 4. Helper Functions

The system also provides additional helper functions for common scenarios:

```javascript
// Show a confirmation dialog
confirmAction('Confirm Action', 'Are you sure you want to proceed?', function() {
    // Code to execute if user confirms
});

// Show loading state
showLoading('Processing request...');

// Hide loading state
hideLoading();
```

## Testing the Notifications

You can test all notification types at `/site/test-alert` which provides a demo page with buttons to trigger different types of alerts.

## Technical Implementation

- The SweetAlert2 JavaScript and CSS are loaded from CDN to prevent asset publishing issues
- The `SweetAlert2Asset` class handles everything, registering both the library and helper functions
- Flash messages are automatically handled and converted to toast notifications
- The standard alert widget (`app\widgets\Alert`) is updated to not display messages that will be shown as SweetAlert notifications

## Troubleshooting

If notifications aren't showing:

1. Make sure the SweetAlert2Asset is registered in the page
2. Check the browser console for any JavaScript errors
3. Verify that flash messages are being set correctly
4. For direct notifications, ensure the JavaScript is executed after the page has loaded

## Best Practices

1. Use `NotificationHelper` for all PHP-initiated notifications
2. Use `showAlert()` for all JavaScript-initiated notifications
3. Keep messages concise and user-friendly
4. Use appropriate notification types (success, error, warning, info)
5. Use promise-based approach for redirects instead of setTimeout 