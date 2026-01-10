<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CategoryBadge from '@/Components/CategoryBadge.vue';
import MutualConnections from '@/Components/MutualConnections.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import VoteReasonSelector from '@/Components/VoteReasonSelector.vue';

const props = defineProps({
    suggestions: Array,
    categories: Array,
});

const selectedCategory = ref(null);
const showScoreBreakdown = ref({});
const selectedCategoryForFollow = ref({});

const filteredSuggestions = computed(() => {
    if (!selectedCategory.value) {
        return props.suggestions;
    }

    return props.suggestions.filter(suggestion => 
        suggestion.categories.some(c => c.slug === selectedCategory.value)
    );
});

const toggleScoreBreakdown = (userId) => {
    showScoreBreakdown.value[userId] = !showScoreBreakdown.value[userId];
};

const followUser = (userId, categoryId = null) => {
    router.post(route('users.follow', userId), {
        category_id: categoryId,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Refresh suggestions after follow
            router.reload({ only: ['suggestions'] });
        },
    });
};

const unfollowUser = (userId) => {
    router.delete(route('users.unfollow', userId), {
        preserveScroll: true,
        onSuccess: () => {
            // Refresh suggestions after unfollow
            router.reload({ only: ['suggestions'] });
        },
    });
};

const refreshSuggestions = () => {
    router.post(route('follow.suggestions.refresh'), {}, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['suggestions'] });
        },
    });
};

const formatScore = (score) => {
    return (score * 100).toFixed(1) + '%';
};
</script>

<template>
    <Head title="Follow Suggestions" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Follow Suggestions
                </h2>
                <PrimaryButton @click="refreshSuggestions" class="text-sm">
                    🔄 Refresh
                </PrimaryButton>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Category Filter -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Filter by Category
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            @click="selectedCategory = null"
                            :class="[
                                'px-3 py-1 rounded-md text-sm transition',
                                selectedCategory === null
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
                            ]"
                        >
                            All
                        </button>
                        <button
                            v-for="category in categories"
                            :key="category.id"
                            @click="selectedCategory = category.slug"
                            :class="[
                                'px-3 py-1 rounded-md text-sm transition flex items-center gap-1',
                                selectedCategory === category.slug
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
                            ]"
                        >
                            <span v-if="category.icon">{{ category.icon }}</span>
                            <span>{{ category.name }}</span>
                        </button>
                    </div>
                </div>

                <!-- Suggestions List -->
                <div class="space-y-4">
                    <div
                        v-for="suggestion in filteredSuggestions"
                        :key="suggestion.user.id"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-6"
                    >
                        <div class="flex items-start justify-between">
                            <!-- User Info -->
                            <div class="flex items-start gap-4 flex-1">
                                <Link
                                    :href="route('profile.show', suggestion.user.id)"
                                    class="h-16 w-16 rounded-full bg-indigo-500 flex items-center justify-center text-white text-lg font-semibold overflow-hidden hover:opacity-80 transition"
                                >
                                    <img
                                        v-if="suggestion.user.avatar_url"
                                        :src="suggestion.user.avatar_url"
                                        :alt="suggestion.user.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <span v-else>{{ suggestion.user.name.charAt(0).toUpperCase() }}</span>
                                </Link>

                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <Link
                                            :href="route('profile.show', suggestion.user.id)"
                                            class="text-lg font-semibold text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400"
                                        >
                                            {{ suggestion.user.business_name || suggestion.user.name }}
                                        </Link>
                                        <span
                                            v-if="suggestion.user.is_verified_mentor"
                                            class="text-xs bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 px-1.5 py-0.5 rounded"
                                            title="Verified user"
                                        >
                                            ✓
                                        </span>
                                    </div>

                                    <p v-if="suggestion.user.business_field" class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                        {{ suggestion.user.business_field }}
                                    </p>

                                    <!-- Categories -->
                                    <div v-if="suggestion.categories.length > 0" class="flex flex-wrap gap-1 mb-2">
                                        <CategoryBadge
                                            v-for="category in suggestion.categories"
                                            :key="category.id"
                                            :category="category"
                                            size="sm"
                                        />
                                    </div>

                                    <!-- Mutual Connections -->
                                    <MutualConnections
                                        v-if="suggestion.mutual_connections_count > 0"
                                        :connections="suggestion.mutual_connections"
                                        :count="suggestion.mutual_connections_count"
                                        :target-user-id="suggestion.user.id"
                                    />
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-col items-end gap-2">
                                <div class="flex gap-2">
                                    <button
                                        @click="toggleScoreBreakdown(suggestion.user.id)"
                                        class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                                        title="View score breakdown"
                                    >
                                        📊 {{ formatScore(suggestion.final_score) }}
                                    </button>
                                </div>

                                <PrimaryButton
                                    @click="followUser(suggestion.user.id)"
                                    class="text-sm"
                                >
                                    Follow
                                </PrimaryButton>
                            </div>
                        </div>

                        <!-- Score Breakdown (Collapsible) -->
                        <div
                            v-if="showScoreBreakdown[suggestion.user.id]"
                            class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"
                        >
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
                                Score Breakdown
                            </h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div class="bg-gray-50 dark:bg-gray-700 rounded p-2">
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Mutual Follows</p>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {{ formatScore(suggestion.scores.mutual_follows) }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500">40% weight</p>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded p-2">
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Engagement</p>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {{ formatScore(suggestion.scores.engagement) }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500">25% weight</p>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded p-2">
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Content Similarity</p>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {{ formatScore(suggestion.scores.content_similarity) }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500">20% weight</p>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded p-2">
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Category Match</p>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {{ formatScore(suggestion.scores.category_match) }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500">15% weight</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="filteredSuggestions.length === 0"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-8 text-center"
                    >
                        <p class="text-gray-500 dark:text-gray-400">
                            No suggestions found{{ selectedCategory ? ' for this category' : '' }}.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

