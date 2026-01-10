<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import WeightedScoreToggle from '@/Components/WeightedScoreToggle.vue';

const props = defineProps({
    post: Object,
    breakdown: Object,
    voters: Array,
    summary: Object,
});

const useWeighted = ref(false);
const activeTab = ref('all'); // 'all', 'upvote', 'downvote'

const filteredVoters = computed(() => {
    if (activeTab.value === 'all') {
        return props.voters;
    }
    return props.voters.filter(v => v.vote_type === activeTab.value);
});

const displayUpvotes = computed(() => {
    return useWeighted.value ? props.summary.weighted_upvotes : props.summary.upvotes;
});

const displayDownvotes = computed(() => {
    return useWeighted.value ? props.summary.weighted_downvotes : props.summary.downvotes;
});

const displayNetScore = computed(() => {
    return useWeighted.value ? props.summary.weighted_net_score : props.summary.net_score;
});

const formatNumber = (num) => {
    if (num === null || num === undefined) return '0';
    return Number.isInteger(num) ? num : num.toFixed(1);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getReasonCount = (voteType, reasonKey) => {
    const reasons = props.breakdown[voteType]?.reasons || {};
    const reason = reasons[reasonKey];
    if (!reason) return 0;
    return useWeighted.value ? reason.weighted_count : reason.count;
};
</script>

<template>
    <Head :title="`Vote Analytics - ${post.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Vote Analytics
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ post.title }}
                    </p>
                </div>
                <Link
                    :href="route('posts.show', post.id)"
                    class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm"
                >
                    ← Back to Post
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Score Toggle -->
                <div class="flex justify-end">
                    <WeightedScoreToggle v-model="useWeighted" />
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Votes</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ summary.total_votes }}
                        </p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Upvotes</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                            👍 {{ formatNumber(displayUpvotes) }}
                        </p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Downvotes</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                            👎 {{ formatNumber(displayDownvotes) }}
                        </p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Net Score</p>
                        <p :class="[
                            'text-2xl font-bold',
                            displayNetScore >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
                        ]">
                            {{ displayNetScore >= 0 ? '+' : '' }}{{ formatNumber(displayNetScore) }}
                        </p>
                    </div>
                </div>

                <!-- Reasons Breakdown -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Upvote Reasons -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            Upvote Reasons
                        </h3>
                        <div class="space-y-3">
                            <div
                                v-for="(data, key) in breakdown.upvote.reasons"
                                :key="key"
                                class="flex items-center justify-between"
                            >
                                <span class="text-gray-700 dark:text-gray-300">{{ data.label }}</span>
                                <div class="flex items-center gap-2">
                                    <div class="w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div
                                            class="bg-green-500 h-2 rounded-full"
                                            :style="{ width: `${(getReasonCount('upvote', key) / (displayUpvotes || 1)) * 100}%` }"
                                        ></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100 w-12 text-right">
                                        {{ formatNumber(getReasonCount('upvote', key)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                                <span>No reason</span>
                                <span class="text-sm">{{ breakdown.upvote.no_reason }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Downvote Reasons -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            Downvote Reasons
                        </h3>
                        <div class="space-y-3">
                            <div
                                v-for="(data, key) in breakdown.downvote.reasons"
                                :key="key"
                                class="flex items-center justify-between"
                            >
                                <span class="text-gray-700 dark:text-gray-300">{{ data.label }}</span>
                                <div class="flex items-center gap-2">
                                    <div class="w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div
                                            class="bg-red-500 h-2 rounded-full"
                                            :style="{ width: `${(getReasonCount('downvote', key) / (displayDownvotes || 1)) * 100}%` }"
                                        ></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100 w-12 text-right">
                                        {{ formatNumber(getReasonCount('downvote', key)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                                <span>No reason</span>
                                <span class="text-sm">{{ breakdown.downvote.no_reason }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Voters List -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Voters
                        </h3>
                        <!-- Tabs -->
                        <div class="flex gap-4 mt-3">
                            <button
                                @click="activeTab = 'all'"
                                :class="[
                                    'px-3 py-1 text-sm rounded-md transition',
                                    activeTab === 'all'
                                        ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300'
                                        : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700'
                                ]"
                            >
                                All ({{ voters.length }})
                            </button>
                            <button
                                @click="activeTab = 'upvote'"
                                :class="[
                                    'px-3 py-1 text-sm rounded-md transition',
                                    activeTab === 'upvote'
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
                                        : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700'
                                ]"
                            >
                                👍 Upvotes ({{ summary.upvotes }})
                            </button>
                            <button
                                @click="activeTab = 'downvote'"
                                :class="[
                                    'px-3 py-1 text-sm rounded-md transition',
                                    activeTab === 'downvote'
                                        ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'
                                        : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700'
                                ]"
                            >
                                👎 Downvotes ({{ summary.downvotes }})
                            </button>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div
                            v-for="voter in filteredVoters"
                            :key="voter.id"
                            class="px-6 py-4 flex items-center justify-between"
                        >
                            <div class="flex items-center gap-3">
                                <Link
                                    v-if="voter.user"
                                    :href="route('profile.show', voter.user.id)"
                                    class="flex items-center gap-3 hover:opacity-80"
                                >
                                    <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm font-semibold overflow-hidden">
                                        <img
                                            v-if="voter.user.avatar_url"
                                            :src="voter.user.avatar_url"
                                            :alt="voter.user.name"
                                            class="w-full h-full object-cover"
                                        />
                                        <span v-else>{{ voter.user.name.charAt(0).toUpperCase() }}</span>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ voter.user.name }}
                                            </span>
                                            <span
                                                v-if="voter.user.is_verified"
                                                class="text-xs bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 px-1.5 py-0.5 rounded"
                                                title="Verified user (2x vote weight)"
                                            >
                                                ✓ 2x
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatDate(voter.voted_at) }}
                                        </p>
                                    </div>
                                </Link>
                                <div v-else class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                                    <span class="text-gray-500 dark:text-gray-400">Deleted user</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <span
                                    v-if="voter.reason_label"
                                    :class="[
                                        'text-xs px-2 py-1 rounded',
                                        voter.vote_type === 'upvote'
                                            ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
                                            : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'
                                    ]"
                                >
                                    {{ voter.reason_label }}
                                </span>
                                <span
                                    :class="[
                                        'text-lg',
                                        voter.vote_type === 'upvote' ? 'text-green-600' : 'text-red-600'
                                    ]"
                                >
                                    {{ voter.vote_type === 'upvote' ? '👍' : '👎' }}
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="filteredVoters.length === 0"
                            class="px-6 py-8 text-center text-gray-500 dark:text-gray-400"
                        >
                            No votes yet.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

