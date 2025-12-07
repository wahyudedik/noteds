<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(__('messages.contact_form_submission')); ?></title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h2 style="color: #2563eb; margin-top: 0;"><?php echo e(__('messages.new_contact_form_submission')); ?></h2>
        <p style="color: #6b7280; margin-bottom: 0;"><?php echo e(__('messages.received_new_message', ['app_name' => config('app.name')])); ?></p>
    </div>

    <div style="background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 10px 0; font-weight: bold; color: #374151; width: 120px;"><?php echo e(__('messages.name')); ?>:</td>
                <td style="padding: 10px 0; color: #111827;"><?php echo e($name); ?></td>
            </tr>
            <tr>
                <td style="padding: 10px 0; font-weight: bold; color: #374151;"><?php echo e(__('messages.email')); ?>:</td>
                <td style="padding: 10px 0; color: #111827;">
                    <a href="mailto:<?php echo e($email); ?>" style="color: #2563eb; text-decoration: none;"><?php echo e($email); ?></a>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0; font-weight: bold; color: #374151;"><?php echo e(__('messages.subject_label')); ?></td>
                <td style="padding: 10px 0; color: #111827;"><?php echo e($subject); ?></td>
            </tr>
            <tr>
                <td style="padding: 10px 0; font-weight: bold; color: #374151; vertical-align: top;"><?php echo e(__('messages.message_label')); ?></td>
                <td style="padding: 10px 0; color: #111827; white-space: pre-wrap;"><?php echo e($message); ?></td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; font-size: 12px;">
        <p style="margin: 0;"><?php echo e(__('messages.email_sent_from_contact_form', ['app_name' => config('app.name')])); ?></p>
        <p style="margin: 5px 0 0 0;"><?php echo e(__('messages.reply_directly_to_email', ['name' => $name])); ?></p>
    </div>
</body>
</html>

<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\emails\contact.blade.php ENDPATH**/ ?>