<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    postId: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['close', 'submit']);

const comment = ref('');
const maxLength = 500;
const remainingChars = computed(() => maxLength - comment.value.length);

const submit = () => {
    if (comment.value.trim()) {
        emit('submit', comment.value);
        comment.value = '';
    }
};

const close = () => {
    comment.value = '';
    emit('close');
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.self="close"
    >
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold mb-4">Add Comment to Repost</h3>
            
            <div class="space-y-4">
                <div>
                    <textarea
                        v-model="comment"
                        rows="4"
                        :maxlength="maxLength"
                        placeholder="Add your comment..."
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300"
                    ></textarea>
                    <div class="text-xs text-gray-500 mt-1 text-right">
                        {{ remainingChars }} characters remaining
                    </div>
                </div>
            </div>
            
            <div class="flex gap-2 mt-6">
                <button
                    @click="close"
                    class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                    Cancel
                </button>
                <button
                    @click="submit"
                    :disabled="!comment.trim()"
                    class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                >
                    Repost
                </button>
            </div>
        </div>
    </div>
</template>

