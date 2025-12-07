<!-- SweetAlert2 Delete Button Examples -->

<!-- Example 1: Simple delete button with data attributes (Recommended for simple cases) -->
<button data-confirm-delete data-delete-url="<?php echo e(route('notes.destroy', $note)); ?>" data-item-name="note"
    class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd"
            d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
            clip-rule="evenodd" />
    </svg>
    Delete
</button>

<!-- Example 2: Delete button with Alpine.js for more control -->
<button @click="deleteItem(<?php echo e($note->id); ?>)"
    class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
    Delete
</button>

<script>
    function deleteItem(noteId) {
        showDeleteConfirm({
            title: 'Delete note?',
            html: 'This note will be permanently deleted and cannot be recovered.'
        }).then(async (result) => {
            if (result.isConfirmed) {
                showLoading('Deleting note...');
                try {
                    const response = await fetch(`/notes/${noteId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content,
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        hideLoading();
                        showSuccess('Note deleted successfully!');
                        setTimeout(() => window.location.href = '/notes', 1500);
                    } else {
                        hideLoading();
                        const error = await response.json();
                        showError(error.message || 'Failed to delete note');
                    }
                } catch (error) {
                    hideLoading();
                    showError('Error: ' + error.message);
                }
            }
        });
    }
</script>

<!-- Example 3: Bulk delete with custom confirmation -->
<button @click="bulkDelete(selectedIds)"
    class="px-3 py-2 text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg">
    Delete Selected
</button>

<script>
    function bulkDelete(ids) {
        if (ids.length === 0) {
            showWarning('Please select items to delete');
            return;
        }

        showConfirm({
            title: `Delete ${ids.length} items?`,
            html: 'This action will permanently delete these items.',
            icon: 'warning',
            confirmButtonText: `Yes, delete ${ids.length} items`,
            confirmButtonColor: '#d33'
        }).then(async (result) => {
            if (result.isConfirmed) {
                showLoading('Deleting items...');
                try {
                    const response = await fetch('/bulk-delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            ids
                        })
                    });

                    if (response.ok) {
                        hideLoading();
                        showSuccess(`${ids.length} items deleted!`);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        hideLoading();
                        showError('Failed to delete items');
                    }
                } catch (error) {
                    hideLoading();
                    showError('Error: ' + error.message);
                }
            }
        });
    }
</script>

<!-- Example 4: Update form with confirmation -->
<form @submit.prevent="submitForm" class="space-y-4">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
        <input v-model="form.title" type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
    </div>

    <div class="flex gap-2">
        <button type="button" @click="submitForm" data-confirm-submit data-confirm-message="Save changes?"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Save Changes
        </button>
        <a href="/notes" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
            Cancel
        </a>
    </div>
</form>

<!-- Example 5: Custom success with redirect -->
<button @click="publishNote" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
    Publish
</button>

<script>
    function publishNote() {
        showConfirm({
            title: 'Publish this note?',
            html: 'Your note will be visible to other users.',
            icon: 'info',
            confirmButtonText: 'Yes, publish'
        }).then(async (result) => {
            if (result.isConfirmed) {
                showLoading('Publishing note...');
                try {
                    const response = await fetch('/api/notes/<?php echo e($note->id); ?>/publish', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content,
                            'Content-Type': 'application/json'
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        hideLoading();
                        showSuccess('Note published! Redirecting...', {
                            timer: 2000
                        });
                        setTimeout(() => window.location.href = data.url, 2000);
                    } else {
                        hideLoading();
                        showError('Failed to publish note');
                    }
                } catch (error) {
                    hideLoading();
                    showError('Error: ' + error.message);
                }
            }
        });
    }
</script>
<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\examples\sweetalert-examples.blade.php ENDPATH**/ ?>