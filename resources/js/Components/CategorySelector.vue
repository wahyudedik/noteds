<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import CategoryBadge from '@/Components/CategoryBadge.vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    userCategories: {
        type: Array,
        default: () => [],
    },
    showInferred: {
        type: Boolean,
        default: true,
    },
    multiple: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['update:modelValue']);

const selectedCategories = ref([...props.modelValue]);
const showDropdown = ref(false);
const searchQuery = ref('');

// Get manual and inferred categories
const manualCategories = computed(() => {
    return props.userCategories.filter(c => c.source === 'manual');
});

const inferredCategories = computed(() => {
    return props.userCategories.filter(c => c.source === 'inferred');
});

// Filter categories based on search
const filteredCategories = computed(() => {
    if (!searchQuery.value) {
        return props.categories;
    }

    const query = searchQuery.value.toLowerCase();
    return props.categories.filter(category => 
        category.name.toLowerCase().includes(query) ||
        category.slug.toLowerCase().includes(query) ||
        (category.description && category.description.toLowerCase().includes(query))
    );
});

// Check if category is selected
const isSelected = (categoryId) => {
    return selectedCategories.value.includes(categoryId);
};

// Toggle category selection
const toggleCategory = (categoryId) => {
    if (!props.multiple) {
        selectedCategories.value = [categoryId];
        showDropdown.value = false;
    } else {
        const index = selectedCategories.value.indexOf(categoryId);
        if (index > -1) {
            selectedCategories.value.splice(index, 1);
        } else {
            selectedCategories.value.push(categoryId);
        }
    }
    
    emit('update:modelValue', [...selectedCategories.value]);
};

// Get selected category objects
const selectedCategoryObjects = computed(() => {
    return props.categories.filter(c => selectedCategories.value.includes(c.id));
});

// Remove category
const removeCategory = (categoryId) => {
    const index = selectedCategories.value.indexOf(categoryId);
    if (index > -1) {
        selectedCategories.value.splice(index, 1);
        emit('update:modelValue', [...selectedCategories.value]);
    }
};

// Sync selectedCategories when modelValue changes externally
watch(() => props.modelValue, (newValue) => {
    selectedCategories.value = [...newValue];
});

onMounted(() => {
    // Initialize with manual categories if provided
    if (props.userCategories && props.userCategories.length > 0) {
        const manualIds = manualCategories.value.map(c => c.id);
        selectedCategories.value = [...new Set([...selectedCategories.value, ...manualIds])];
        emit('update:modelValue', [...selectedCategories.value]);
    }
});
</script>

<template>
    <div class="relative">
        <!-- Selected Categories Display -->
        <div
            @click="showDropdown = !showDropdown"
            class="min-h-[42px] w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 flex items-center gap-2 flex-wrap cursor-pointer hover:border-indigo-500 dark:hover:border-indigo-400 transition"
        >
            <div v-if="selectedCategoryObjects.length > 0" class="flex flex-wrap gap-1 flex-1">
                <CategoryBadge
                    v-for="category in selectedCategoryObjects"
                    :key="category.id"
                    :category="category"
                    size="sm"
                    :show-source="showInferred"
                    :source="userCategories.find(uc => uc.id === category.id)?.source"
                    class="cursor-pointer hover:opacity-75"
                    @click.stop="removeCategory(category.id)"
                />
            </div>
            <span
                v-else
                class="text-gray-500 dark:text-gray-400 text-sm"
            >
                Select categories...
            </span>
            <svg
                class="w-4 h-4 text-gray-500 dark:text-gray-400 transition-transform"
                :class="{ 'rotate-180': showDropdown }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>

        <!-- Inferred Categories (Read-only) -->
        <div
            v-if="showInferred && inferredCategories.length > 0"
            class="mt-2 flex flex-wrap gap-1"
        >
            <span class="text-xs text-gray-600 dark:text-gray-400">Auto-detected:</span>
            <CategoryBadge
                v-for="category in inferredCategories"
                :key="category.id"
                :category="category"
                size="sm"
                show-source
                source="inferred"
            />
        </div>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="showDropdown"
                class="absolute z-50 mt-1 w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg max-h-60 overflow-auto"
                @click.stop
            >
                <!-- Search Input -->
                <div class="p-2 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800">
                    <input
                        id="category-search"
                        name="category_search"
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search categories..."
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200"
                        autocomplete="off"
                        @click.stop
                    />
                </div>

                <!-- Category List -->
                <div class="py-1">
                    <div
                        v-for="category in filteredCategories"
                        :key="category.id"
                        @click="toggleCategory(category.id)"
                        :class="[
                            'px-4 py-2 text-sm cursor-pointer hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition',
                            isSelected(category.id) && 'bg-indigo-100 dark:bg-indigo-900/30'
                        ]"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span v-if="category.icon" class="text-lg">{{ category.icon }}</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ category.name }}</span>
                            </div>
                            <svg
                                v-if="isSelected(category.id)"
                                class="w-5 h-5 text-indigo-600 dark:text-indigo-400"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <p v-if="category.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1 ml-7">
                            {{ category.description }}
                        </p>
                    </div>

                    <div
                        v-if="filteredCategories.length === 0"
                        class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400"
                    >
                        No categories found
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Click outside to close -->
        <div
            v-if="showDropdown"
            class="fixed inset-0 z-40"
            @click="showDropdown = false"
        ></div>
    </div>
</template>

