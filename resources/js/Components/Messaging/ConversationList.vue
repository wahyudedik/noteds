<template>
    <div class="flex-1 overflow-y-auto" ref="listRef" @scroll.passive="onScroll" role="navigation" aria-label="Conversation list">
    <div class="p-2 border-b">
            <input
                type="search"
                v-model="q"
                placeholder="Search conversations"
                class="w-full border rounded px-3 py-2 text-sm"
                aria-label="Search conversations"
            />
        </div>
        <div v-if="initialLoading" class="p-2 space-y-2">
            <div v-for="i in 6" :key="'s-'+i" class="animate-pulse flex items-center gap-3 p-3 border-b">
                <div class="w-10 h-10 rounded-full bg-gray-300"></div>
                <div class="flex-1 space-y-2">
                    <div class="w-40 h-3 bg-gray-300 rounded"></div>
                    <div class="w-64 h-3 bg-gray-200 rounded"></div>
                </div>
            </div>
        </div>
        <div v-if="filtered.length > 0" role="list">
            <div :style="{ height: topSpacer + 'px' }"></div>
            <ConversationItem
                v-for="conversation in visibleItems"
                :key="conversation.id"
                :conversation="conversation"
                :active="conversation.id === activeConversationId"
                role="listitem"
            />
            <div :style="{ height: bottomSpacer + 'px' }"></div>
        </div>
        <div v-else class="p-4 text-center text-gray-500">
            <p>No conversations yet</p>
        </div>
        <div v-if="loading" class="p-3 text-center text-gray-400 text-xs" aria-live="polite">Loading...</div>
    </div>
 </template>

<script setup>
import ConversationItem from './ConversationItem.vue';

const props = defineProps({
    conversations: Object,
    activeConversationId: String,
});

import { ref, onMounted, computed } from 'vue';
const listRef = ref(null);
const items = ref([]);
const q = ref('');
const page = ref(1);
const loading = ref(false);
const hasMore = ref(true);
const prefetching = ref(false);
const prefetched = ref(null);
const rowHeight = 68;
const startIndex = ref(0);
const endIndex = ref(0);
const initialLoading = ref(true);

onMounted(() => {
    const initial = props.conversations?.data || [];
    items.value = initial.slice();
    initialLoading.value = false;
    const currentPage = props.conversations?.current_page || 1;
    page.value = currentPage;
    const lastPage = props.conversations?.last_page || 1;
    hasMore.value = currentPage < lastPage;
    prefetchNext();
    updateWindow();
});

const filtered = computed(() => {
    const term = q.value.trim().toLowerCase();
    if (!term) return items.value;
    return items.value.filter(c => {
        const name = (c.display_name || c.name || '').toLowerCase();
        const preview = (c.messages?.data?.[0]?.content || c.last_message?.content || '').toLowerCase();
        return name.includes(term) || preview.includes(term);
    });
});

const visibleItems = computed(() => filtered.value.slice(startIndex.value, Math.min(endIndex.value, filtered.value.length)));
const topSpacer = computed(() => startIndex.value * rowHeight);
const bottomSpacer = computed(() => Math.max(0, (filtered.value.length - endIndex.value) * rowHeight));

const onScroll = async () => {
    if (loading.value || !hasMore.value) return;
    const el = listRef.value;
    if (!el) return;
    updateWindow();
    if (el.scrollTop + el.clientHeight >= el.scrollHeight - 300) {
        if (prefetched.value) {
            items.value = [...items.value, ...prefetched.value.data];
            page.value = prefetched.value.current_page;
            const lastPage = prefetched.value.last_page || page.value;
            hasMore.value = page.value < lastPage;
            prefetched.value = null;
            prefetchNext();
        } else {
            await loadMore();
        }
    }
};

const updateWindow = () => {
    const el = listRef.value;
    if (!el) return;
    const viewport = el.clientHeight;
    const scrollTop = el.scrollTop;
    const buffer = 5;
    const newStart = Math.max(0, Math.floor(scrollTop / rowHeight) - buffer);
    const newEnd = Math.ceil((scrollTop + viewport) / rowHeight) + buffer;
    startIndex.value = newStart;
    endIndex.value = newEnd;
};

const loadMore = async () => {
    loading.value = true;
    try {
        const next = page.value + 1;
        const url = route('messaging.conversations.index');
        const res = await fetch(`${url}?page=${next}`, {
            headers: { 'Accept': 'application/json' },
            credentials: 'include',
        });
        if (!res.ok) {
            hasMore.value = false;
            return;
        }
        const data = await res.json();
        const newItems = data?.data || [];
        if (newItems.length === 0) {
            hasMore.value = false;
        } else {
            items.value = [...items.value, ...newItems];
            page.value = data.current_page || next;
            const lastPage = data.last_page || page.value;
            hasMore.value = page.value < lastPage;
            prefetchNext();
        }
    } catch (e) {
        hasMore.value = false;
    } finally {
        loading.value = false;
    }
};

const prefetchNext = async () => {
    if (prefetching.value || !hasMore.value) return;
    prefetching.value = true;
    try {
        const next = page.value + 1;
        const url = route('messaging.conversations.index');
        const res = await fetch(`${url}?page=${next}`, {
            headers: { 'Accept': 'application/json' },
            credentials: 'include',
        });
        if (!res.ok) return;
        const data = await res.json();
        const newItems = data?.data || [];
        if (newItems.length === 0) return;
        prefetched.value = data;
    } catch (e) {
    } finally {
        prefetching.value = false;
    }
};
</script>
