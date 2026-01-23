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
            <div v-else-if="item.mime_type.startsWith('audio/') || item.is_encrypted" class="space-y-2 p-2 bg-black bg-opacity-20 rounded">
                <audio ref="audioRefs[item.id]" :src="getAudioSrc(item)" controls class="w-full"></audio>
                <AudioWaveform :audio="audioRefs[item.id]" :waveform="item.waveform || []" />
                <div class="flex items-center justify-between">
                  <span class="text-xs">{{ formatDuration(item.duration) }}</span>
                </div>
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
import { reactive, onMounted } from 'vue';
import AudioWaveform from './AudioWaveform.vue';
import { importKeyFromBase64, decryptArrayBuffer } from '@/Utils/encryption';

const props = defineProps({
    media: Array,
    autoPlayEnabled: { type: Boolean, default: false },
    createdAt: String,
});

const audioRefs = reactive({});

const openImage = (url) => {
    window.open(url, '_blank');
};

// native controls are used

onMounted(() => {
    if (!props.autoPlayEnabled) return;
    const now = Date.now();
    const created = props.createdAt ? new Date(props.createdAt).getTime() : 0;
    const isRecent = created && (now - created) < 5000;
    if (!isRecent) return;
    // Find first audio
    const audioIds = Object.keys(audioRefs);
    for (const id of audioIds) {
        const audio = audioRefs[id];
        if (audio && typeof audio.play === 'function') {
            // Optional: battery check
            try {
                if (navigator.getBattery) {
                    navigator.getBattery().then(b => {
                        if (b.level < 0.2) return;
                        audio.play();
                    });
                } else {
                    audio.play();
                }
            } catch (e) {}
            break;
        }
    }
});

const getAudioSrc = (item) => {
    if (item.is_encrypted && window.__conversationKey && crypto?.subtle) {
        // fetch and decrypt, return blob URL
        const keyPromise = importKeyFromBase64(window.__conversationKey);
        fetch(item.url)
            .then(r => r.arrayBuffer())
            .then(async buf => {
                const key = await keyPromise;
                const pt = await decryptArrayBuffer(buf, key);
                const blob = new Blob([pt], { type: 'audio/webm' });
                const url = URL.createObjectURL(blob);
                const audio = audioRefs[item.id];
                if (audio) audio.src = url;
            })
            .catch(() => {});
        return '';
    }
    return item.url;
};

const formatDuration = (seconds) => {
    if (!seconds) return '0:00';
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};
</script>

