<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    filters: {
        type: Object,
        default: () => ({}),
    },
    query: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['filters-changed']);

const localFilters = ref({
    date: props.filters?.date || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    author: props.filters?.author || '',
    hashtags: props.filters?.hashtags || '',
    purpose_type: props.filters?.purpose_type || 'all',
    min_engagement: props.filters?.min_engagement || '',
    sort_by: props.filters?.sort_by || 'latest',
});

const showAdvanced = ref(false);

const applyFilters = () => {
    const params = {
        q: props.query,
        ...localFilters.value,
    };

    // Remove empty values
    Object.keys(params).forEach(key => {
        if (params[key] === '' || params[key] === null || params[key] === 'all') {
            delete params[key];
        }
    });

    router.get(route('search.index'), params, {
        preserveState: true,
        preserveScroll: true,
    });

    emit('filters-changed', localFilters.value);
};

const resetFilters = () => {
    localFilters.value = {
        date: '',
        date_from: '',
        date_to: '',
        author: '',
        hashtags: '',
        purpose_type: 'all',
        min_engagement: '',
        sort_by: 'latest',
    };
    applyFilters();
};

// Watch for filter changes and apply with debounce
let debounceTimer = null;
watch(localFilters, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        applyFilters();
    }, 500);
}, { deep: true });
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Advanced Filters
            </h3>
            <button
                @click="showAdvanced = !showAdvanced"
                class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300"
            >
                {{ showAdvanced ? 'Hide' : 'Show' }} Advanced
            </button>
        </div>

        <!-- Quick Filters -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <!-- Purpose Type -->
            <div>
                <InputLabel for="purpose_type" value="Post Type" />
                <select
                    id="purpose_type"
                    v-model="localFilters.purpose_type"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                >
                    <option value="all">All Types</option>
                    <option value="idea_business">Idea/Business</option>
                    <option value="ask_question">Ask Question</option>
                    <option value="share_experience">Share Experience</option>
                    <option value="find_partner">Find Partner</option>
                    <option value="find_tools">Find Tools</option>
                    <option value="validate_idea">Validate Idea</option>
                </select>
            </div>

            <!-- Sort By -->
            <div>
                <InputLabel for="sort_by" value="Sort By" />
                <select
                    id="sort_by"
                    v-model="localFilters.sort_by"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                >
                    <option value="latest">Latest</option>
                    <option value="oldest">Oldest</option>
                    <option value="trending">Trending</option>
                    <option value="most_engaged">Most Engaged</option>
                    <option value="most_upvoted">Most Upvoted</option>
                    <option value="most_commented">Most Commented</option>
                </select>
            </div>

            <!-- Date Filter -->
            <div>
                <InputLabel for="date" value="Date Range" />
                <select
                    id="date"
                    v-model="localFilters.date"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                >
                    <option value="">All Time</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="year">This Year</option>
                </select>
            </div>
        </div>

        <!-- Advanced Filters -->
        <div v-if="showAdvanced" class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
            <!-- Custom Date Range -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="date_from" value="From Date" />
                    <TextInput
                        id="date_from"
                        v-model="localFilters.date_from"
                        type="date"
                        class="mt-1 block w-full"
                    />
                </div>
                <div>
                    <InputLabel for="date_to" value="To Date" />
                    <TextInput
                        id="date_to"
                        v-model="localFilters.date_to"
                        type="date"
                        class="mt-1 block w-full"
                    />
                </div>
            </div>

            <!-- Author -->
            <div>
                <InputLabel for="author" value="Author" />
                <TextInput
                    id="author"
                    v-model="localFilters.author"
                    type="text"
                    placeholder="Search by author name..."
                    class="mt-1 block w-full"
                />
            </div>

            <!-- Hashtags -->
            <div>
                <InputLabel for="hashtags" value="Hashtags" />
                <TextInput
                    id="hashtags"
                    v-model="localFilters.hashtags"
                    type="text"
                    placeholder="e.g., #startup #business (comma separated)"
                    class="mt-1 block w-full"
                />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Enter hashtags separated by commas
                </p>
            </div>

            <!-- Min Engagement -->
            <div>
                <InputLabel for="min_engagement" value="Minimum Engagement" />
                <TextInput
                    id="min_engagement"
                    v-model="localFilters.min_engagement"
                    type="number"
                    min="0"
                    placeholder="e.g., 10"
                    class="mt-1 block w-full"
                />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Minimum total engagement (votes + comments + reposts)
                </p>
            </div>
        </div>

        <!-- Reset Button -->
        <div class="mt-4 flex justify-end">
            <button
                @click="resetFilters"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                Reset Filters
            </button>
        </div>
    </div>
</template>


