<template>
    <div class="space-y-2">
        <div v-for="item in media" :key="item.id" class="relative">
            <img
                v-if="item.mime_type.startsWith('image/')"
                :src="item.url"
                :alt="item.file_name"
                class="max-w-full rounded-lg cursor-pointer"
                @click="openImage(item.url)"
            />
            <div v-else-if="item.mime_type.startsWith('audio/')" class="flex items-center space-x-2 p-2 bg-black bg-opacity-20 rounded">
                <button @click="playAudio(item)" class="text-2xl">▶️</button>
                <span class="text-sm">{{ formatDuration(item.duration) }}</span>
            </div>
            <a
                v-else
                :href="item.url"
                :download="item.file_name"
                class="flex items-center space-x-2 p-2 bg-black bg-opacity-20 rounded hover:bg-opacity-30"
            >
                <span>📎</span>
                <span class="text-sm truncate">{{ item.file_name }}</span>
            </a>
        </div>
    </div>
</template>

<script setup>
defineProps({
    media: Array,
});

const openImage = (url) => {
    window.open(url, '_blank');
};

const playAudio = (item) => {
    const audio = new Audio(item.url);
    audio.play();
};

const formatDuration = (seconds) => {
    if (!seconds) return '0:00';
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};
</script>

