<template>
    <div v-if="typingUsers.length > 0" class="px-4 py-2 text-sm text-gray-500 italic">
        <span v-if="typingUsers.length === 1">
            {{ typingUsers[0].user.name }} is typing...
        </span>
        <span v-else-if="typingUsers.length === 2">
            {{ typingUsers[0].user.name }} and {{ typingUsers[1].user.name }} are typing...
        </span>
        <span v-else>
            {{ typingUsers.length }} people are typing...
        </span>
    </div>
</template>

<script setup>
defineProps({
    typingUsers: {
        type: Array,
        default: () => [],
    },
});
</script>

