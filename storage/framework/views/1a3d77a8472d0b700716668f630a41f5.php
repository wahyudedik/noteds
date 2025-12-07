<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(__('You Received a Gift Note!')); ?></title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px;">🎁 <?php echo e(config('app.name', 'Noteds')); ?></h1>
    </div>
    
    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e5e7eb;">
        <h2 style="color: #1f2937; margin-top: 0; font-size: 24px;">
            <?php echo e(__('You Received a Gift Note!')); ?>

        </h2>
        
        <p style="font-size: 16px; color: #374151;">
            <?php echo e(__('Hello')); ?>,
        </p>
        
        <p style="font-size: 16px; color: #374151;">
            <strong><?php echo e($gifter->name); ?></strong> <?php echo e(__('has sent you a gift note!')); ?>

        </p>
        
        <div style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea; margin: 20px 0;">
            <h3 style="color: #1f2937; margin-top: 0; font-size: 18px;"><?php echo e($note->title); ?></h3>
            <?php if($note->summary): ?>
                <p style="color: #6b7280; margin: 10px 0;"><?php echo e(Str::limit(strip_tags($note->summary), 200)); ?></p>
            <?php endif; ?>
            <p style="color: #059669; font-weight: bold; margin: 10px 0 0 0;">
                <?php echo e(__('Price')); ?>: <?php echo e(currency($note->price)); ?>

            </p>
        </div>
        
        <?php if($giftNote->message): ?>
            <div style="background: #fef3c7; padding: 20px; border-radius: 8px; border: 1px solid #fbbf24; margin: 20px 0;">
                <p style="margin: 0; font-size: 15px; color: #92400e; font-style: italic;">
                    "<?php echo e($giftNote->message); ?>"
                </p>
                <p style="margin: 10px 0 0 0; font-size: 14px; color: #92400e;">
                    — <?php echo e($gifter->name); ?>

                </p>
            </div>
        <?php endif; ?>
        
        <p style="font-size: 16px; color: #374151;">
            <?php echo e(__('Click the button below to claim your gift note. The note will be added to your library once claimed.')); ?>

        </p>
        
        <?php if($giftNote->expires_at): ?>
            <p style="font-size: 14px; color: #dc2626; margin: 10px 0;">
                ⏰ <?php echo e(__('This gift expires on')); ?> <?php echo e($giftNote->expires_at->format('F d, Y')); ?>.
            </p>
        <?php endif; ?>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="<?php echo e($claimUrl); ?>" 
               style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                <?php echo e(__('Claim Your Gift Note')); ?> 🎁
            </a>
        </div>
        
        <div style="background: #e0e7ff; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 0; font-size: 14px; color: #3730a3;">
                💡 <strong><?php echo e(__('Tip')); ?>:</strong> <?php echo e(__('You must have an account on')); ?> <?php echo e(config('app.name')); ?> <?php echo e(__('to claim this gift. If you don\'t have an account yet, you can create one for free!')); ?>

            </p>
        </div>
        
        <p style="color: #6b7280; font-size: 14px; margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 20px;">
            <?php echo e(__('If you have any questions, please contact our support team.')); ?>

        </p>
        
        <p style="color: #6b7280; font-size: 12px; margin-top: 20px; text-align: center;">
            © <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?>. <?php echo e(__('All rights reserved.')); ?>

        </p>
    </div>
</body>
</html>

<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\emails\gift-note-received.blade.php ENDPATH**/ ?>