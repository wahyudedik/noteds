<template>
    <div class="border-b border-gray-200 p-4 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <img
                :src="conversation.display_avatar || '/default-avatar.png'"
                :alt="conversation.display_name"
                class="w-10 h-10 rounded-full"
            />
            <div>
                <h2 class="font-semibold">{{ conversation.display_name }}</h2>
                <p v-if="conversation.type === 'group'" class="text-sm text-gray-500">
                    {{ conversation.active_participants?.length || 0 }} participants
                </p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <button class="p-2 text-gray-500 hover:text-gray-700">
                ⚙️
            </button>
        </div>
    </div>
</template>

<script setup>
defineProps({
    conversation: Object,
});
</script>

