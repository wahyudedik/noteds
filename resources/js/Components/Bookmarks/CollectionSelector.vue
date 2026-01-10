<script setup>
import { ref, computed } from 'vue';
import CollectionTree from './CollectionTree.vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: null,
    },
    collections: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue']);

const showDropdown = ref(false);
const selectedCollection = computed(() => {
    if (!props.modelValue) return null;
    return findCollection(props.collections, props.modelValue);
});

const findCollection = (items, id) => {
    for (const item of items) {
        if (item.id === id) return item;
        if (item.children) {
            const found = findCollection(item.children, id);
            if (found) return found;
        }
    }
    return null;
};

const selectCollection = (collection) => {
    emit('update:modelValue', collection.id);
    showDropdown.value = false;
};

const clearSelection = () => {
    emit('update:modelValue', null);
    showDropdown.value = false;
};
</script>

<template>
    <div class="relative">
        <button
            @click="showDropdown = !showDropdown"
            class="w-full px-3 py-2 text-left border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 flex items-center justify-between"
        >
            <span class="text-sm">
                {{ selectedCollection ? selectedCollection.name : 'Select Collection' }}
            </span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        
        <div
            v-if="showDropdown"
            class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg max-h-64 overflow-auto"
            @click.stop
        >
            <div class="p-2">
                <button
                    @click="clearSelection"
                    class="w-full text-left px-2 py-1.5 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
                >
                    None
                </button>
                <CollectionTree
                    :collections="collections"
                    :selected-id="modelValue"
                    @select="selectCollection"
                />
            </div>
        </div>
        
        <div
            v-if="showDropdown"
            class="fixed inset-0 z-40"
            @click="showDropdown = false"
        ></div>
    </div>
</template>

