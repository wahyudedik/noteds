<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['name', 'label' => null, 'value' => null, 'required' => false, 'rows' => 4]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['name', 'label' => null, 'value' => null, 'required' => false, 'rows' => 4]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="mb-4">
    <?php if($label): ?>
        <label for="<?php echo e($name); ?>" class="block font-medium text-sm text-gray-700">
            <?php echo e($label); ?> <?php if($required): ?>
                <span class="text-red-600">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>

    <textarea id="<?php echo e($name); ?>" name="<?php echo e($name); ?>" rows="<?php echo e($rows); ?>"
        <?php echo e($attributes->class([
            'mt-1 block w-full rounded-md border-gray-300 shadow-sm',
            'ring-1 ring-red-500' => $errors->has($name),
        ])); ?>

        <?php if($required): ?> required <?php endif; ?>><?php echo e(old($name, $value)); ?></textarea>

    <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>
<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\components\form\textarea.blade.php ENDPATH**/ ?>