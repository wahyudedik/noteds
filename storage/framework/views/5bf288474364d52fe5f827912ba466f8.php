<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($note->title); ?> - Noteds</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
        }
        .header {
            border-bottom: 2px solid #3B82F6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        h1 {
            color: #1F2937;
            font-size: 28px;
            margin: 0 0 10px 0;
        }
        .meta {
            color: #6B7280;
            font-size: 14px;
            margin: 10px 0;
        }
        .content {
            margin-top: 30px;
        }
        .content p {
            margin-bottom: 15px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            color: #6B7280;
            font-size: 12px;
        }
        @media print {
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><?php echo e($note->title); ?></h1>
        <div class="meta">
            <p><strong>Author:</strong> <?php echo e($note->user->name); ?></p>
            <p><strong>Published:</strong> <?php echo e($note->created_at->format('F d, Y')); ?></p>
            <?php if($note->summary): ?>
                <p><strong>Summary:</strong> <?php echo e($note->summary); ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="content">
        <?php echo $note->content; ?>

    </div>
    
    <div class="footer">
        <p>Exported from Noteds - <?php echo e(now()->format('F d, Y H:i')); ?></p>
        <p>© <?php echo e(date('Y')); ?> Noteds. All rights reserved.</p>
    </div>
</body>
</html>

<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\exports\note-pdf.blade.php ENDPATH**/ ?>