<script setup>
defineProps({
    users: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div class="space-y-3">
        <a
            v-for="user in users"
            :key="user.id"
            :href="route('profile.show', user.id)"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition"
        >
            <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold overflow-hidden flex-shrink-0">
                <img
                    v-if="user.avatar_url"
                    :src="user.avatar_url"
                    :alt="user.business_name || user.name"
                    class="w-full h-full object-cover"
                />
                <span v-else>
                    {{ (user.business_name || user.name).charAt(0).toUpperCase() }}
                </span>
            </div>
            <div class="flex-1">
                <div class="font-medium text-gray-900 dark:text-gray-100">
                    {{ user.business_name || user.name }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ user.business_field || 'Business' }}
                </div>
            </div>
        </a>
    </div>
</template>
