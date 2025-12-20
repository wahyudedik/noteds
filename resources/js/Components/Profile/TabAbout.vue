<script setup>
defineProps({
    profileUser: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <!-- Skills -->
        <div v-if="profileUser.skills && profileUser.skills.length > 0" class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Skills</h3>
            <div class="flex flex-wrap gap-2">
                <span
                    v-for="(skill, index) in profileUser.skills"
                    :key="index"
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200"
                >
                    {{ skill }}
                </span>
            </div>
        </div>

        <!-- Goals -->
        <div v-if="profileUser.goals && profileUser.goals.length > 0" class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Goals</h3>
            <ul class="space-y-2">
                <li
                    v-for="(goal, index) in profileUser.goals"
                    :key="index"
                    class="flex items-start gap-2 text-gray-700 dark:text-gray-300"
                >
                    <svg class="w-5 h-5 text-indigo-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ goal }}</span>
                </li>
            </ul>
        </div>

        <!-- Contact Info -->
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Contact Information</h3>
            <div class="space-y-2">
                <div class="flex items-center gap-3 text-gray-700 dark:text-gray-300">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>{{ profileUser.email }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

