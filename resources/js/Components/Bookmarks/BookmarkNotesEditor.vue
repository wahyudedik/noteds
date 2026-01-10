<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { marked } from 'marked';

// Configure marked
marked.setOptions({
    breaks: true,
    gfm: true,
});

const props = defineProps({
    bookmark: {
        type: Object,
        required: true,
    },
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'updated']);

const form = useForm({
    notes: '',
});

const showPreview = ref(false);
const previewHtml = ref('');

watch(() => props.bookmark, (newVal) => {
    if (newVal) {
        form.notes = newVal.notes || '';
        updatePreview();
    }
}, { immediate: true });

watch(() => form.notes, () => {
    updatePreview();
});

const updatePreview = () => {
    previewHtml.value = marked(form.notes || '');
};

const save = () => {
    form.put(route('bookmarks.notes.update', props.bookmark.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('updated');
        },
    });
};

const close = () => {
    form.reset();
    emit('close');
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.self="close"
    >
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full p-6 max-h-[80vh] flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Bookmark Notes</h3>
                <div class="flex gap-2">
                    <button
                        @click="showPreview = !showPreview"
                        class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        {{ showPreview ? 'Edit' : 'Preview' }}
                    </button>
                    <button
                        @click="close"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                    >
                        ✕
                    </button>
                </div>
            </div>
            
            <div class="flex-1 overflow-auto">
                <textarea
                    v-if="!showPreview"
                    v-model="form.notes"
                    rows="15"
                    placeholder="Add your notes here (Markdown supported)..."
                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300 font-mono text-sm"
                ></textarea>
                <div
                    v-else
                    class="prose dark:prose-invert max-w-none"
                    v-html="previewHtml"
                ></div>
            </div>
            
            <div class="flex gap-2 mt-4">
                <button
                    @click="close"
                    class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                    Cancel
                </button>
                <button
                    @click="save"
                    :disabled="form.processing"
                    class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                >
                    Save
                </button>
            </div>
        </div>
    </div>
</template>

