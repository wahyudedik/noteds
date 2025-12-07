<?php $__env->startSection('title', 'Edit Contest'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="<?php echo e(route('admin.contests.index')); ?>" class="text-blue-600 hover:text-blue-800">
                ← Back to Contests
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Edit Contest</h2>
            </div>
            <div class="p-6">
                <form action="<?php echo e(route('admin.contests.update', $contest)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>

                    <div class="space-y-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                Title <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="title" id="title" value="<?php echo e(old('title', $contest->title)); ?>" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description <span class="text-red-600">*</span>
                            </label>
                            <textarea name="description" id="description" rows="4" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo e(old('description', $contest->description)); ?></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                                    Type <span class="text-red-600">*</span>
                                </label>
                                <select name="type" id="type" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="monthly" <?php echo e(old('type', $contest->type) === 'monthly' ? 'selected' : ''); ?>>Monthly Challenge</option>
                                    <option value="themed" <?php echo e(old('type', $contest->type) === 'themed' ? 'selected' : ''); ?>>Themed Contest</option>
                                    <option value="custom" <?php echo e(old('type', $contest->type) === 'custom' ? 'selected' : ''); ?>>Custom Contest</option>
                                </select>
                            </div>

                            <div id="theme-field" style="<?php echo e(old('type', $contest->type) === 'themed' ? 'display: block;' : 'display: none;'); ?>">
                                <label for="theme" class="block text-sm font-medium text-gray-700 mb-2">
                                    Theme
                                </label>
                                <input type="text" name="theme" id="theme" value="<?php echo e(old('theme', $contest->theme)); ?>"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Status <span class="text-red-600">*</span>
                            </label>
                            <select name="status" id="status" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="draft" <?php echo e(old('status', $contest->status) === 'draft' ? 'selected' : ''); ?>>Draft</option>
                                <option value="open" <?php echo e(old('status', $contest->status) === 'open' ? 'selected' : ''); ?>>Open</option>
                                <option value="voting" <?php echo e(old('status', $contest->status) === 'voting' ? 'selected' : ''); ?>>Voting</option>
                                <option value="closed" <?php echo e(old('status', $contest->status) === 'closed' ? 'selected' : ''); ?>>Closed</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Start Date
                                </label>
                                <input type="datetime-local" name="start_date" id="start_date" 
                                    value="<?php echo e(old('start_date', $contest->start_date ? $contest->start_date->format('Y-m-d\TH:i') : '')); ?>"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    End Date
                                </label>
                                <input type="datetime-local" name="end_date" id="end_date"
                                    value="<?php echo e(old('end_date', $contest->end_date ? $contest->end_date->format('Y-m-d\TH:i') : '')); ?>"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="voting_start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Voting Start Date
                                </label>
                                <input type="datetime-local" name="voting_start_date" id="voting_start_date"
                                    value="<?php echo e(old('voting_start_date', $contest->voting_start_date ? $contest->voting_start_date->format('Y-m-d\TH:i') : '')); ?>"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="voting_end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Voting End Date
                                </label>
                                <input type="datetime-local" name="voting_end_date" id="voting_end_date"
                                    value="<?php echo e(old('voting_end_date', $contest->voting_end_date ? $contest->voting_end_date->format('Y-m-d\TH:i') : '')); ?>"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="max_entries_per_user" class="block text-sm font-medium text-gray-700 mb-2">
                                Max Entries Per User <span class="text-red-600">*</span>
                            </label>
                            <input type="number" name="max_entries_per_user" id="max_entries_per_user" 
                                value="<?php echo e(old('max_entries_per_user', $contest->max_entries_per_user)); ?>" min="1" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Prizes (JSON format)
                            </label>
                            <textarea name="prizes_json" id="prizes_json" rows="6"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-sm"
                                placeholder='[{"position": 1, "type": "cash", "value": 100}, {"position": 2, "type": "badge", "badge_id": "..."}]'><?php echo e(old('prizes_json', $contest->prizes ? json_encode($contest->prizes, JSON_PRETTY_PRINT) : '')); ?></textarea>
                            <p class="mt-1 text-sm text-gray-500">Format: [{"position": 1, "type": "cash", "value": 100}, {"type": "badge", "badge_id": "..."}]</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Rules (one per line)
                            </label>
                            <textarea name="rules_text" id="rules_text" rows="4"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Rule 1&#10;Rule 2&#10;Rule 3"><?php echo e(old('rules_text', $contest->rules ? implode("\n", $contest->rules) : '')); ?></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="<?php echo e(route('admin.contests.index')); ?>" 
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" 
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md">
                                Update Contest
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('type').addEventListener('change', function() {
    const themeField = document.getElementById('theme-field');
    if (this.value === 'themed') {
        themeField.style.display = 'block';
    } else {
        themeField.style.display = 'none';
    }
});

// Convert prizes JSON and rules text to arrays on submit
document.querySelector('form').addEventListener('submit', function(e) {
    const prizesJson = document.getElementById('prizes_json').value;
    const rulesText = document.getElementById('rules_text').value;

    if (prizesJson) {
        try {
            const prizes = JSON.parse(prizesJson);
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'prizes';
            hiddenInput.value = JSON.stringify(prizes);
            this.appendChild(hiddenInput);
        } catch (e) {
            alert('Invalid JSON format for prizes');
            e.preventDefault();
            return false;
        }
    }

    if (rulesText) {
        const rules = rulesText.split('\n').filter(r => r.trim());
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'rules';
        hiddenInput.value = JSON.stringify(rules);
        this.appendChild(hiddenInput);
    }
});
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\contests\edit.blade.php ENDPATH**/ ?>