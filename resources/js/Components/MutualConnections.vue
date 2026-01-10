<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    connections: {
        type: Array,
        default: () => [],
    },
    count: {
        type: Number,
        default: 0,
    },
    showLimit: {
        type: Number,
        default: 5,
    },
    showMore: {
        type: Boolean,
        default: false,
    },
    targetUserId: {
        type: String,
        default: null,
    },
});

const displayedConnections = computed(() => {
    if (props.showMore) {
        return props.connections;
    }
    return props.connections.slice(0, props.showLimit);
});

const hasMore = computed(() => {
    return props.connections.length > props.showLimit;
});
</script>

<template>
    <div class="space-y-2">
        <div v-if="count > 0" class="flex items-center gap-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ count }} Mutual {{ count === 1 ? 'Connection' : 'Connections' }}
            </span>
        </div>

        <div v-if="displayedConnections.length > 0" class="flex flex-wrap gap-2">
            <Link
                v-for="connection in displayedConnections"
                :key="connection.id"
                :href="route('profile.show', connection.id)"
                class="flex items-center gap-2 hover:opacity-80 transition"
            >
                <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-semibold overflow-hidden">
                    <img
                        v-if="connection.avatar_url"
                        :src="connection.avatar_url"
                        :alt="connection.name"
                        class="w-full h-full object-cover"
                    />
                    <span v-else>{{ connection.name.charAt(0).toUpperCase() }}</span>
                </div>
                <span class="text-sm text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400">
                    {{ connection.name }}
                </span>
            </Link>

            <Link
                v-if="hasMore && !showMore && targetUserId"
                :href="route('users.mutual-connections', targetUserId)"
                class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline"
            >
                +{{ count - showLimit }} more
            </Link>
        </div>

        <div
            v-else-if="count === 0"
            class="text-sm text-gray-500 dark:text-gray-400"
        >
            No mutual connections
        </div>
    </div>
</template>

