<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    collection: {
        type: Object,
        default: null,
    },
    collections: {
        type: Array,
        default: () => [],
    },
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'saved']);

const form = useForm({
    name: '',
    description: '',
    parent_id: null,
    icon: '',
    color: '',
});

const colors = [
    '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
    '#EC4899', '#06B6D4', '#84CC16', '#F97316', '#6366F1',
];

watch(() => props.collection, (newVal) => {
    if (newVal) {
        form.name = newVal.name || '';
        form.description = newVal.description || '';
        form.parent_id = newVal.parent_id || null;
        form.icon = newVal.icon || '';
        form.color = newVal.color || '';
    } else {
        form.reset();
    }
}, { immediate: true });

const submit = () => {
    const url = props.collection
        ? route('bookmarks.collections.update', props.collection.id)
        : route('bookmarks.collections.store');
    
    const method = props.collection ? 'put' : 'post';
    
    form[method](url, {
        preserveScroll: true,
        onSuccess: () => {
            emit('saved');
            form.reset();
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
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold mb-4">
                {{ collection ? 'Edit Collection' : 'Create Collection' }}
            </h3>
            
            <form @submit.prevent="submit">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300"
                        />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea
                            v-model="form.description"
                            rows="2"
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300"
                        ></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Parent Collection</label>
                        <select
                            v-model="form.parent_id"
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300"
                        >
                            <option :value="null">None (Root)</option>
                            <option
                                v-for="col in collections"
                                :key="col.id"
                                :value="col.id"
                                v-if="!collection || col.id !== collection.id"
                            >
                                {{ col.name }}
                            </option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Icon (Emoji)</label>
                        <input
                            v-model="form.icon"
                            type="text"
                            maxlength="10"
                            placeholder="📁"
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300"
                        />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Color</label>
                        <div class="flex gap-2 flex-wrap">
                            <button
                                v-for="color in colors"
                                :key="color"
                                type="button"
                                @click="form.color = color"
                                class="w-8 h-8 rounded-full border-2 transition"
                                :class="form.color === color ? 'border-gray-900 dark:border-gray-100 scale-110' : 'border-gray-300 dark:border-gray-600'"
                                :style="{ backgroundColor: color }"
                            ></button>
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-2 mt-6">
                    <button
                        type="button"
                        @click="close"
                        class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ collection ? 'Update' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

