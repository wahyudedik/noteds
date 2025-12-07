<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Don't Miss Out!</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px;">📚 <?php echo e(config('app.name', 'Noteds')); ?></h1>
    </div>
    
    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e5e7eb;">
        <h2 style="color: #1f2937; margin-top: 0; font-size: 24px;">
            Don't Miss Out!
        </h2>
        
        <p style="font-size: 16px; color: #374151;">
            Hello<?php echo e($user ? ' ' . $user->name : ''); ?>,
        </p>
        
        <p style="font-size: 16px; color: #374151;">
            We noticed you were interested in this note but haven't purchased it yet. Here's a reminder:
        </p>
        
        <div style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea; margin: 20px 0;">
            <h3 style="color: #1f2937; margin-top: 0; font-size: 18px;"><?php echo e($note->title); ?></h3>
            <?php if($note->summary): ?>
                <p style="color: #6b7280; margin: 10px 0;"><?php echo e(Str::limit(strip_tags($note->summary), 200)); ?></p>
            <?php endif; ?>
            <p style="color: #059669; font-weight: bold; margin: 10px 0 0 0;">
                Price: <?php echo e(currency($note->price)); ?>

            </p>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="<?php echo e($noteUrl); ?>" 
               style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                View Note
            </a>
        </div>
        
        <p style="color: #6b7280; font-size: 14px; margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 20px;">
            If you have any questions, please contact our support team.
        </p>
        
        <?php if(isset($unsubscribeUrl)): ?>
            <p style="color: #6b7280; font-size: 12px; margin-top: 20px; text-align: center;">
                <a href="<?php echo e($unsubscribeUrl); ?>" style="color: #6b7280; text-decoration: underline;">Unsubscribe</a> | 
                © <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?>. All rights reserved.
            </p>
        <?php else: ?>
            <p style="color: #6b7280; font-size: 12px; margin-top: 20px; text-align: center;">
                © <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?>. All rights reserved.
            </p>
        <?php endif; ?>
    </div>
    
    <?php if(isset($trackingPixel)): ?>
        <img src="<?php echo e($trackingPixel); ?>" width="1" height="1" style="display:none;" alt="" />
    <?php endif; ?>
</body>
</html>

<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\emails\abandoned-cart.blade.php ENDPATH**/ ?>