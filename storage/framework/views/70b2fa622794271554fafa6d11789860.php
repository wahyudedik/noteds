<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your Weekly Digest</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px;">📚 <?php echo e(config('app.name', 'Noteds')); ?></h1>
    </div>
    
    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e5e7eb;">
        <h2 style="color: #1f2937; margin-top: 0; font-size: 24px;">
            Your Weekly Digest
        </h2>
        
        <p style="font-size: 16px; color: #374151;">
            Hello <?php echo e($user->name); ?>,
        </p>
        
        <?php if(!empty($summary)): ?>
        <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #667eea;">
            <h3 style="color: #1f2937; margin-top: 0;">Summary</h3>
            <p style="margin: 5px 0;"><strong>Total Notifications:</strong> <?php echo e($summary['total'] ?? 0); ?></p>
            <?php if(!empty($summary['by_type'])): ?>
                <?php $__currentLoopData = $summary['by_type']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <p style="margin: 5px 0;"><strong><?php echo e(ucfirst(str_replace('_', ' ', $type))); ?>:</strong> <?php echo e($count); ?></p>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if(!empty($notifications)): ?>
        <h3 style="color: #1f2937; margin-top: 20px;">Your Notifications This Week</h3>
        <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div style="background: white; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #3b82f6;">
                <h4 style="color: #1f2937; margin: 0 0 5px 0;"><?php echo e($notification['title'] ?? 'Notification'); ?></h4>
                <p style="color: #6b7280; margin: 0; font-size: 14px;"><?php echo e($notification['message'] ?? ''); ?></p>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>

        <p style="font-size: 16px; color: #374151; margin-top: 30px;">
            Here are some recommended notes for you this week:
        </p>
        
        <?php if($recommendedNotes && $recommendedNotes->count() > 0): ?>
            <?php $__currentLoopData = $recommendedNotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea; margin: 20px 0;">
                    <h3 style="color: #1f2937; margin-top: 0; font-size: 18px;"><?php echo e($note->title); ?></h3>
                    <?php if($note->summary): ?>
                        <p style="color: #6b7280; margin: 10px 0;"><?php echo e(Str::limit(strip_tags($note->summary), 150)); ?></p>
                    <?php endif; ?>
                    <p style="color: #059669; font-weight: bold; margin: 10px 0 0 0;">
                        Price: <?php echo e(currency($note->price)); ?>

                    </p>
                    <a href="<?php echo e(route('marketplace.show', $note)); ?>" 
                       style="display: inline-block; color: #667eea; text-decoration: none; margin-top: 10px; font-weight: bold;">
                        View Note →
                    </a>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php else: ?>
            <p style="color: #6b7280; font-style: italic;">
                No new recommendations this week. Check back next week!
            </p>
        <?php endif; ?>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="<?php echo e(route('marketplace.index')); ?>" 
               style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                Browse Marketplace
            </a>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="<?php echo e(route('notifications.index')); ?>" 
               style="display: inline-block; background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;">
                View All Notifications
            </a>
        </div>

        <p style="color: #6b7280; font-size: 14px; margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 20px;">
            You're receiving this weekly digest because you have it enabled. 
            <a href="<?php echo e(route('notifications.preferences.index')); ?>" style="color: #667eea;">Manage your notification preferences</a>.
        </p>
        
        <p style="color: #6b7280; font-size: 12px; margin-top: 20px; text-align: center;">
            © <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?>. All rights reserved.
        </p>
    </div>
</body>
</html>

<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\emails\weekly-digest.blade.php ENDPATH**/ ?>