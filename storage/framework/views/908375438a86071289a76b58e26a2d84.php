<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Digest - <?php echo e(config('app.name')); ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #ffffff;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }
        .notification-item {
            padding: 15px;
            margin-bottom: 15px;
            background: #f9fafb;
            border-left: 4px solid #667eea;
            border-radius: 4px;
        }
        .notification-item.unread {
            background: #eff6ff;
            border-left-color: #3b82f6;
        }
        .notification-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 5px;
        }
        .notification-message {
            color: #6b7280;
            font-size: 14px;
        }
        .notification-time {
            color: #9ca3af;
            font-size: 12px;
            margin-top: 8px;
        }
        .summary {
            background: #f3f4f6;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .summary-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 10px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .summary-item:last-child {
            border-bottom: none;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 12px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📧 Your Daily Digest</h1>
        <p style="margin: 0; opacity: 0.9;"><?php echo e(config('app.name')); ?></p>
    </div>

    <div class="content">
        <p>Hello <?php echo e($user->name); ?>,</p>
        
        <p>Here's a summary of your notifications from the last 24 hours:</p>

        <?php if(!empty($summary)): ?>
        <div class="summary">
            <div class="summary-title">Summary</div>
            <div class="summary-item">
                <span>Total Notifications:</span>
                <strong><?php echo e($summary['total'] ?? 0); ?></strong>
            </div>
            <?php if(!empty($summary['by_type'])): ?>
                <?php $__currentLoopData = $summary['by_type']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="summary-item">
                    <span><?php echo e(ucfirst(str_replace('_', ' ', $type))); ?>:</span>
                    <strong><?php echo e($count); ?></strong>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <h2 style="color: #1f2937; margin-top: 30px; margin-bottom: 15px;">Your Notifications</h2>

        <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="notification-item">
            <div class="notification-title"><?php echo e($notification['title'] ?? 'Notification'); ?></div>
            <div class="notification-message"><?php echo e($notification['message'] ?? ''); ?></div>
            <?php if(isset($notification['created_at'])): ?>
            <div class="notification-time"><?php echo e(\Carbon\Carbon::parse($notification['created_at'])->diffForHumans()); ?></div>
            <?php endif; ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="notification-item">
            <div class="notification-message">No new notifications in the last 24 hours.</div>
        </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 30px;">
            <a href="<?php echo e(route('notifications.index')); ?>" class="button">View All Notifications</a>
        </div>

        <p style="margin-top: 30px; color: #6b7280; font-size: 14px;">
            You're receiving this email because you have daily digest enabled. 
            <a href="<?php echo e(route('notifications.preferences.index')); ?>" style="color: #667eea;">Manage your preferences</a> to change this.
        </p>
    </div>

    <div class="footer">
        <p>&copy; <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?>. All rights reserved.</p>
        <p>
            <a href="<?php echo e(route('notifications.preferences.index')); ?>" style="color: #667eea;">Notification Preferences</a> | 
            <a href="<?php echo e(url('/')); ?>" style="color: #667eea;">Visit Website</a>
        </p>
    </div>
</body>
</html>

<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\emails\daily-digest.blade.php ENDPATH**/ ?>