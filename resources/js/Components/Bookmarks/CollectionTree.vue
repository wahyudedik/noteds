<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    collections: {
        type: Array,
        default: () => [],
    },
    selectedId: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['select', 'edit', 'delete']);

const expanded = ref({});

const toggleExpand = (collectionId) => {
    expanded.value[collectionId] = !expanded.value[collectionId];
};

const selectCollection = (collection) => {
    emit('select', collection);
};

const editCollection = (collection, event) => {
    event.stopPropagation();
    emit('edit', collection);
};

const deleteCollection = (collection, event) => {
    event.stopPropagation();
    if (confirm(`Delete collection "${collection.name}"?`)) {
        emit('delete', collection);
    }
};

const renderTree = (items, level = 0) => {
    return items.map(item => ({
        ...item,
        level,
        children: item.children ? renderTree(item.children, level + 1) : [],
    }));
};

const treeItems = computed(() => renderTree(props.collections));
</script>

<template>
    <div class="space-y-1">
        <div
            v-for="collection in treeItems"
            :key="collection.id"
            class="collection-item"
        >
            <div
                @click="selectCollection(collection)"
                :class="[
                    'flex items-center gap-2 px-2 py-1.5 rounded cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition',
                    selectedId === collection.id && 'bg-indigo-100 dark:bg-indigo-900/30'
                ]"
                :style="{ paddingLeft: `${collection.level * 1 + 0.5}rem` }"
            >
                <button
                    v-if="collection.children && collection.children.length > 0"
                    @click.stop="toggleExpand(collection.id)"
                    class="w-4 h-4 flex items-center justify-center"
                >
                    <svg
                        class="w-3 h-3 transition-transform"
                        :class="{ 'rotate-90': expanded[collection.id] }"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                <span v-else class="w-4"></span>
                
                <span v-if="collection.icon" class="text-sm">{{ collection.icon }}</span>
                <span
                    v-if="collection.color"
                    class="w-3 h-3 rounded-full"
                    :style="{ backgroundColor: collection.color }"
                ></span>
                <span class="flex-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ collection.name }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ collection.bookmarks_count || 0 }}
                </span>
            </div>
            
            <div
                v-if="expanded[collection.id] && collection.children && collection.children.length > 0"
                class="ml-4"
            >
                <CollectionTree
                    :collections="collection.children"
                    :selected-id="selectedId"
                    @select="(c) => emit('select', c)"
                    @edit="(c, e) => emit('edit', c, e)"
                    @delete="(c, e) => emit('delete', c, e)"
                />
            </div>
        </div>
    </div>
</template>

