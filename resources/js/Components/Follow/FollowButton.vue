<script setup>
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    userId: {
        type: String,
        required: true,
    },
    isFollowing: {
        type: Boolean,
        default: false,
    },
    canFollow: {
        type: Boolean,
        default: true,
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg
    },
});

const emit = defineEmits(['followed', 'unfollowed']);

const buttonClasses = computed(() => {
    const base = 'inline-flex items-center justify-center rounded-md font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2';
    const sizeClasses = {
        sm: 'px-3 py-1.5 text-sm',
        md: 'px-4 py-2 text-sm',
        lg: 'px-6 py-3 text-base',
    };
    
    if (props.isFollowing) {
        return `${base} ${sizeClasses[props.size]} bg-gray-200 text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 focus:ring-gray-500`;
    } else {
        return `${base} ${sizeClasses[props.size]} bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500`;
    }
});

const follow = () => {
    if (!props.canFollow) return;
    
    router.post(route('users.follow', props.userId), {}, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            emit('followed');
        },
    });
};

const unfollow = () => {
    router.delete(route('users.unfollow', props.userId), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            emit('unfollowed');
        },
    });
};

const handleClick = () => {
    if (props.isFollowing) {
        unfollow();
    } else {
        follow();
    }
};
</script>

<template>
    <button
        @click="handleClick"
        :disabled="!canFollow"
        :class="[
            buttonClasses,
            !canFollow && 'opacity-50 cursor-not-allowed'
        ]"
    >
        <svg
            v-if="!isFollowing"
            class="-ml-1 mr-2 h-4 w-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <svg
            v-else
            class="-ml-1 mr-2 h-4 w-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        {{ isFollowing ? 'Following' : 'Follow' }}
    </button>
</template>

