<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title); ?></title>
</head>
<body style="font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif; background: #f6f8fa; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
                    <tr>
                        <td style="padding: 20px 24px; background: #0ea5e9; color: #ffffff;">
                            <h1 style="margin: 0; font-size: 18px;"><?php echo e($title); ?></h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 24px;">
                            <p style="margin: 0 0 12px 0; color: #111827; font-size: 14px; line-height: 1.5;">
                                <?php echo e($messageText); ?>

                            </p>
                            <?php if(!empty($actionUrl)): ?>
                                <p style="margin: 20px 0 0 0;">
                                    <a href="<?php echo e($actionUrl); ?>" style="background: #0ea5e9; color: #ffffff; padding: 10px 16px; text-decoration: none; border-radius: 6px; font-size: 14px;">
                                        Lihat Detail
                                    </a>
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 16px 24px; background: #f9fafb; color: #6b7280; font-size: 12px;">
                            Email ini dikirim otomatis oleh Noteds Studio.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>


<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\emails\studio\notification.blade.php ENDPATH**/ ?>