<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(__('messages.subscription_renewal_failed_subject')); ?></title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 24px;"><?php echo e(config('app.name', 'Noteds')); ?></h1>
    </div>
    
    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e5e7eb;">
        <h2 style="color: #dc2626; margin-top: 0; font-size: 20px;">
            ⚠️ <?php echo e(__('messages.subscription_renewal_failed_title')); ?>

        </h2>
        
        <p style="font-size: 16px; color: #374151;">
            <?php echo e(__('messages.subscription_renewal_failed_greeting', ['name' => $user->name])); ?>

        </p>
        
        <div style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #dc2626; margin: 20px 0;">
            <p style="margin: 0; font-size: 15px; color: #374151;">
                <?php echo e(__('messages.subscription_renewal_failed_message')); ?>

            </p>
        </div>
        
        <div style="background: #fef3c7; padding: 20px; border-radius: 8px; border: 1px solid #fbbf24; margin: 20px 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #374151;"><?php echo e(__('messages.required_amount')); ?>:</td>
                    <td style="padding: 8px 0; text-align: right; color: #dc2626; font-size: 18px; font-weight: bold;">
                        <?php echo e(currency($requiredAmount)); ?>

                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #374151;"><?php echo e(__('messages.current_balance')); ?>:</td>
                    <td style="padding: 8px 0; text-align: right; color: #6b7280;">
                        <?php echo e(currency($currentBalance)); ?>

                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-top: 12px; border-top: 1px solid #e5e7eb;">
                        <strong style="color: #dc2626;">
                            <?php echo e(__('messages.insufficient_amount')); ?>: 
                            <?php echo e(currency($requiredAmount - $currentBalance)); ?>

                        </strong>
                    </td>
                </tr>
            </table>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="<?php echo e($topUpUrl); ?>" 
               style="display: inline-block; background: #2563eb; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">
                <?php echo e(__('messages.top_up_wallet_now')); ?>

            </a>
        </div>
        
        <div style="background: #e0e7ff; padding: 15px; border-radius: 8px; margin-top: 20px;">
            <p style="margin: 0; font-size: 14px; color: #4338ca;">
                <strong><?php echo e(__('messages.note')); ?>:</strong> 
                <?php echo e(__('messages.subscription_renewal_failed_note')); ?>

            </p>
        </div>
        
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
        
        <p style="font-size: 14px; color: #6b7280; text-align: center; margin: 0;">
            <?php echo e(__('messages.need_help')); ?> 
            <a href="<?php echo e(route('contact.index')); ?>" style="color: #2563eb; text-decoration: none;"><?php echo e(__('messages.contact_us')); ?></a>
        </p>
    </div>
    
    <div style="text-align: center; margin-top: 20px; color: #6b7280; font-size: 12px;">
        <p><?php echo e(__('messages.email_footer', ['year' => date('Y'), 'app' => config('app.name', 'Noteds')])); ?></p>
    </div>
</body>
</html>

<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\emails\subscription-renewal-failed.blade.php ENDPATH**/ ?>