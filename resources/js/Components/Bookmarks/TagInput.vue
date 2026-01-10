<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    userTags: {
        type: Array,
        default: () => [],
    },
    globalTags: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue']);

const inputValue = ref('');
const showSuggestions = ref(false);
const suggestions = ref([]);
const selectedTags = ref([...props.modelValue]);

const allTags = computed(() => [
    ...props.userTags,
    ...props.globalTags.filter(t => !props.userTags.find(ut => ut.id === t.id)),
]);

watch(() => props.modelValue, (newVal) => {
    selectedTags.value = [...newVal];
});

const searchSuggestions = async () => {
    if (inputValue.value.length < 1) {
        suggestions.value = [];
        showSuggestions.value = false;
        return;
    }

    try {
        const response = await router.get(route('bookmarks.tags.suggestions'), {
            q: inputValue.value,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
        
        suggestions.value = response.data.suggestions || [];
        showSuggestions.value = true;
    } catch (error) {
        console.error('Error fetching suggestions:', error);
    }
};

const addTag = (tag) => {
    if (!selectedTags.value.find(t => t.id === tag.id)) {
        selectedTags.value.push(tag);
        emit('update:modelValue', selectedTags.value);
    }
    inputValue.value = '';
    showSuggestions.value = false;
};

const createTag = () => {
    if (inputValue.value.trim()) {
        const newTag = {
            id: 'temp-' + Date.now(),
            name: inputValue.value.trim(),
            is_global: false,
        };
        addTag(newTag);
    }
};

const removeTag = (tagId) => {
    selectedTags.value = selectedTags.value.filter(t => t.id !== tagId);
    emit('update:modelValue', selectedTags.value);
};

const handleKeydown = (event) => {
    if (event.key === 'Enter') {
        event.preventDefault();
        if (suggestions.value.length > 0) {
            addTag(suggestions.value[0]);
        } else {
            createTag();
        }
    }
};
</script>

<template>
    <div class="relative">
        <div class="flex flex-wrap gap-1 p-2 border border-gray-300 dark:border-gray-700 rounded-md min-h-[42px]">
            <span
                v-for="tag in selectedTags"
                :key="tag.id"
                class="inline-flex items-center gap-1 px-2 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded text-sm"
            >
                {{ tag.name }}
                <span
                    v-if="tag.is_global"
                    class="text-xs opacity-75"
                    title="Global tag"
                >
                    🌐
                </span>
                <button
                    @click="removeTag(tag.id)"
                    class="ml-1 hover:opacity-75"
                >
                    ×
                </button>
            </span>
            <input
                id="tag-input"
                name="tag_input"
                v-model="inputValue"
                @input="searchSuggestions"
                @keydown="handleKeydown"
                @focus="showSuggestions = inputValue.length > 0"
                type="text"
                placeholder="Add tags..."
                class="flex-1 min-w-[120px] border-0 focus:ring-0 bg-transparent"
                autocomplete="off"
            />
        </div>
        
        <div
            v-if="showSuggestions && suggestions.length > 0"
            class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg max-h-48 overflow-auto"
        >
            <div
                v-for="tag in suggestions"
                :key="tag.id"
                @click="addTag(tag)"
                class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer flex items-center justify-between"
            >
                <span>{{ tag.name }}</span>
                <span
                    v-if="tag.is_global"
                    class="text-xs text-gray-500"
                    title="Global tag"
                >
                    🌐
                </span>
            </div>
        </div>
    </div>
</template>

