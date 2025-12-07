<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Undangan Workspace - Noteds</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px;">Noteds</h1>
    </div>
    
    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px;">
        <h2 style="color: #1f2937; margin-top: 0;">Anda Diundang Bergabung!</h2>
        
        <p>Halo,</p>
        
        <p>Anda telah diundang untuk bergabung dengan workspace <strong><?php echo e($workspace->name); ?></strong> di Noteds.</p>
        
        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea;">
            <p style="margin: 0;"><strong>Workspace:</strong> <?php echo e($workspace->name); ?></p>
            <p style="margin: 5px 0 0 0;"><strong>Role:</strong> <?php echo e($invitation->role === 'admin' ? 'Admin' : 'Member'); ?></p>
            <?php if($workspace->description): ?>
                <p style="margin: 10px 0 0 0; color: #6b7280;"><?php echo e($workspace->description); ?></p>
            <?php endif; ?>
        </div>
        
        <p>Untuk menerima undangan ini, silakan klik tombol di bawah ini dan daftar dengan email <strong><?php echo e($invitation->email); ?></strong>:</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="<?php echo e($inviteLink); ?>" 
               style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                Terima Undangan & Daftar
            </a>
        </div>
        
        <p style="color: #6b7280; font-size: 14px; margin-top: 30px;">
            Atau salin dan tempel link berikut di browser Anda:<br>
            <a href="<?php echo e($inviteLink); ?>" style="color: #667eea; word-break: break-all;"><?php echo e($inviteLink); ?></a>
        </p>
        
        <p style="color: #6b7280; font-size: 12px; margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 20px;">
            <strong>Catatan:</strong> Undangan ini akan kadaluarsa pada <?php echo e($invitation->expires_at->format('d M Y, H:i')); ?>.
            Jika Anda tidak ingin bergabung, Anda dapat mengabaikan email ini.
        </p>
        
        <p style="margin-top: 30px;">
            Salam,<br>
            <strong>Tim Noteds</strong>
        </p>
    </div>
</body>
</html>
<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\emails\workspace-invitation.blade.php ENDPATH**/ ?>