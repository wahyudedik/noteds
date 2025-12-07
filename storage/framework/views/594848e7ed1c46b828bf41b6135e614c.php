<?php $__env->startComponent('mail::message'); ?>
# <?php echo e($subject); ?>


<?php echo e($messageBody); ?>


<?php if($actionUrl): ?>
<?php $__env->startComponent('mail::button', ['url' => $actionUrl]); ?>
<?php echo e(__('chat.view_conversation')); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>

<?php echo e(__('chat.email_footer')); ?>


Thanks,<br>
<?php echo e(config('app.name')); ?>

<?php echo $__env->renderComponent(); ?>

<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\emails\chat\notification.blade.php ENDPATH**/ ?>