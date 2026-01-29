<template>
    <div class="border-b border-gray-200 p-4 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <template v-if="conversation.display_avatar">
                <img
                    :src="conversation.display_avatar"
                    :alt="conversation.display_name"
                    class="w-10 h-10 rounded-full object-cover"
                />
            </template>
            <template v-else>
                <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-semibold">
                    {{ initials }}
                </div>
            </template>
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
import { computed } from 'vue';
const props = defineProps({
    conversation: Object,
});
const initials = computed(() => {
    const name = (props.conversation?.display_name || '').trim();
    if (!name) return '?';
    const parts = name.split(/\s+/).slice(0, 2);
    return parts.map(p => p[0]?.toUpperCase()).join('');
});
</script>

