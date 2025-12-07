<button <?php echo e($attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex items-center justify-center rounded-full bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:opacity-60 disabled:cursor-not-allowed'
])); ?>>
    <?php echo e($slot); ?>

</button>
<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\components\primary-button.blade.php ENDPATH**/ ?>