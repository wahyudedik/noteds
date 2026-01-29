<template>
    <div class="flex-1 overflow-y-auto p-4 space-y-4" ref="messageListRef" @scroll.passive="onScroll" role="feed" aria-label="Conversation messages">
        <div ref="topSentinel"></div>
        <div v-if="initialLoading" class="space-y-3">
            <div v-for="i in 6" :key="i" class="animate-pulse flex items-start gap-2">
                <div class="w-7 h-7 rounded-full bg-gray-300"></div>
                <div class="space-y-2">
                    <div class="w-48 h-3 bg-gray-300 rounded"></div>
                    <div class="w-64 h-3 bg-gray-200 rounded"></div>
                </div>
            </div>
        </div>
        <div v-if="renderBlocks.length > 0">
            <template v-for="block in renderBlocks" :key="block.key">
                <div v-if="block.type === 'date'" class="text-center text-xs text-gray-500 my-2" aria-live="polite">
                    {{ block.label }}
                </div>
                <MessageItem
                    v-else
                    :message="block.message"
                    :conversation="conversation"
                    :autoPlayEnabled="autoPlayEnabled"
                />
            </template>
        </div>
        <div v-else class="text-center text-gray-500 py-8">
            <p>No messages yet. Start the conversation!</p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import MessageItem from './MessageItem.vue';

const props = defineProps({
    messages: {
        type: [Object, Array],
        default: () => [],
    },
    conversation: Object,
    lastReadAt: {
        type: String,
        default: null,
    },
    autoPlayEnabled: {
        type: Boolean,
        default: false,
    },
});

const messageListRef = ref(null);
const topSentinel = ref(null);
const loadingMore = ref(false);
const hasMore = ref(true);
const allMessages = ref([]);
const initialLoading = ref(true);

const displayMessages = computed(() => {
    return allMessages.value;
});

const renderBlocks = computed(() => {
    const res = [];
    let lastDate = null;
    const lra = props.lastReadAt ? new Date(props.lastReadAt) : null;
    let unreadInserted = false;
    for (const msg of displayMessages.value) {
        const d = new Date(msg.created_at);
        const dayKey = d.toISOString().slice(0,10);
        if (dayKey !== lastDate) {
            res.push({ type: 'date', label: d.toLocaleDateString(), key: `date-${dayKey}` });
            lastDate = dayKey;
        }
        if (lra && !unreadInserted && d > lra) {
            res.push({ type: 'date', label: 'Unread', key: `unread-${dayKey}` });
            unreadInserted = true;
        }
        res.push({ type: 'message', message: msg, key: `msg-${msg.id}` });
    }
    return res;
});

const scrollToBottom = () => {
    nextTick(() => {
        if (messageListRef.value) {
            messageListRef.value.scrollTop = messageListRef.value.scrollHeight;
        }
    });
};

onMounted(() => {
    const initial = Array.isArray(props.messages) ? props.messages : (props.messages?.data || []);
    allMessages.value = initial.slice().reverse().reverse(); // clone
    scrollToBottom();
    initialLoading.value = false;
});

watch(() => displayMessages.value, () => {
    scrollToBottom();
}, { deep: true });

const onScroll = async () => {
    if (loadingMore.value || !hasMore.value) return;
    const el = messageListRef.value;
    if (!el) return;
    if (el.scrollTop < 100) {
        await loadOlder();
    }
};

const loadOlder = async () => {
    loadingMore.value = true;
    try {
        const oldest = allMessages.value[0];
        const before = oldest?.created_at;
        const url = route('messaging.messages.index', props.conversation.id);
        const res = await fetch(`${url}?limit=20${before ? `&before=${encodeURIComponent(before)}` : ''}`, {
            headers: { 'Accept': 'application/json' },
            credentials: 'include',
        });
        if (!res.ok) {
            hasMore.value = false;
            return;
        }
        const data = await res.json();
        const items = Array.isArray(data?.data) ? data.data : [];
        if (items.length === 0) {
            hasMore.value = false;
        } else {
            allMessages.value = [...items, ...allMessages.value];
        }
    } catch (e) {
        hasMore.value = false;
    } finally {
        loadingMore.value = false;
    }
};
</script>
