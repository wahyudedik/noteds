<template>
    <div class="border-t border-gray-200 p-4">
        <form @submit.prevent="sendMessage" class="flex items-center space-x-2">
            <input
                type="text"
                v-model="form.content"
                @input="handleTyping"
                @keydown.enter.exact.prevent="sendMessage"
                placeholder="Type a message..."
                class="flex-1 rounded-md border-gray-300"
            />
            <button
                type="button"
                @click="toggleFileUpload"
                class="p-2 text-gray-500 hover:text-gray-700"
            >
                📎
            </button>
            <button
                type="button"
                @click="toggleRecording"
                class="p-2 text-gray-500 hover:text-gray-700"
                :disabled="recording.processing"
                title="Voice message"
            >
                🎤
            </button>
            <input
                ref="fileInput"
                type="file"
                multiple
                @change="handleFileSelect"
                class="hidden"
                accept="image/*,application/pdf,.doc,.docx,audio/*"
            />
            <button
                type="submit"
                :disabled="form.processing || (!form.content && !form.attachments.length)"
                class="px-4 py-2 bg-blue-600 text-white rounded-md disabled:opacity-50"
            >
                Send
            </button>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { importKeyFromBase64, encryptBlob } from '@/Utils/encryption';

const props = defineProps({
    conversation: Object,
});

const form = useForm({
    content: '',
    attachments: [],
    reply_to_id: null,
});

const fileInput = ref(null);
let typingTimeout = null;

const toggleFileUpload = () => {
    fileInput.value?.click();
};

const handleFileSelect = (event) => {
    form.attachments = Array.from(event.target.files);
};

const sendMessage = () => {
    const formData = new FormData();
    formData.append('content', form.content || '');
    formData.append('reply_to_id', form.reply_to_id || '');
    
    if (form.attachments && form.attachments.length > 0) {
        form.attachments.forEach((file) => {
            formData.append('attachments[]', file);
        });
    }

    form.transform(() => formData).post(route('messaging.messages.store', props.conversation.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('content', 'attachments');
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
    });
};

const handleTyping = () => {
    // Send typing indicator
    router.post(route('messaging.typing.start', props.conversation.id), {}, {
        preserveState: true,
        preserveScroll: true,
    });

    // Clear existing timeout
    if (typingTimeout) {
        clearTimeout(typingTimeout);
    }

    // Stop typing after 3 seconds of inactivity
    typingTimeout = setTimeout(() => {
        router.post(route('messaging.typing.stop', props.conversation.id), {}, {
            preserveState: true,
            preserveScroll: true,
        });
    }, 3000);
};

// Voice recording
const recording = ref({
    isRecording: false,
    chunks: [],
    mediaRecorder: null,
    startTime: 0,
    elapsed: 0,
    timer: null,
    previewUrl: null,
    processing: false,
});

const toggleRecording = async () => {
    if (!recording.value.isRecording) {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            const mr = new MediaRecorder(stream, { mimeType: chooseMimeType(), audioBitsPerSecond: 128000 });
            recording.value.mediaRecorder = mr;
            recording.value.chunks = [];
            recording.value.elapsed = 0;
            recording.value.previewUrl = null;
            mr.ondataavailable = (e) => {
                if (e.data && e.data.size > 0) {
                    recording.value.chunks.push(e.data);
                }
            };
            mr.onstop = () => {
                const blob = new Blob(recording.value.chunks, { type: mr.mimeType });
                recording.value.previewUrl = URL.createObjectURL(blob);
            };
            mr.start();
            recording.value.isRecording = true;
            recording.value.startTime = Date.now();
            recording.value.timer = setInterval(() => {
                recording.value.elapsed = Math.floor((Date.now() - recording.value.startTime) / 1000);
                if (recording.value.elapsed >= 120) {
                    stopRecording();
                }
            }, 200);
        } catch (e) {
            console.error(e);
        }
    } else {
        stopRecording();
    }
};

const stopRecording = () => {
    if (recording.value.mediaRecorder && recording.value.mediaRecorder.state !== 'inactive') {
        recording.value.mediaRecorder.stop();
    }
    recording.value.isRecording = false;
    if (recording.value.timer) clearInterval(recording.value.timer);
};

const chooseMimeType = () => {
    const types = ['audio/webm', 'audio/ogg'];
    for (const t of types) {
        if (MediaRecorder.isTypeSupported(t)) return t;
    }
    return '';
};

const sendVoice = async () => {
    if (!recording.value.previewUrl) return;
    recording.value.processing = true;
    const blob = await fetch(recording.value.previewUrl).then(r => r.blob());
    let encrypted = false;
    let uploadBlob = blob;
    try {
        if (window.__conversationKey && crypto?.subtle) {
            const key = await importKeyFromBase64(window.__conversationKey);
            uploadBlob = await encryptBlob(blob, key);
            encrypted = true;
        }
    } catch (e) {
        encrypted = false;
    }
    const file = new File([uploadBlob], `voice_${Date.now()}.${encrypted ? 'bin' : 'webm'}`, { type: uploadBlob.type || 'audio/webm' });
    const fd = new FormData();
    fd.append('voice', file);
    fd.append('duration', String(recording.value.elapsed));
    fd.append('audio_codec', blob.type);
    fd.append('reply_to_id', form.reply_to_id || '');
    // Compute waveform summary
    try {
        const wf = await computeWaveform(blob);
        fd.append('waveform', JSON.stringify(wf));
    } catch (e) {}
    if (encrypted) {
        fd.append('encrypted', '1');
    }
    try {
        await router.post(route('messaging.messages.voice.store', props.conversation.id), fd, {
            preserveScroll: true,
            onSuccess: () => {
                recording.value.processing = false;
                recording.value.previewUrl = null;
                recording.value.chunks = [];
                recording.value.elapsed = 0;
            },
            onError: () => {
                recording.value.processing = false;
            },
        });
    } catch (e) {
        recording.value.processing = false;
    }
};

const computeWaveform = async (blob) => {
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    const arrBuf = await blob.arrayBuffer();
    const audioBuf = await audioCtx.decodeAudioData(arrBuf);
    const channelData = audioBuf.getChannelData(0);
    const samples = 500; // downsample to 500 bars
    const blockSize = Math.floor(channelData.length / samples);
    const waveform = [];
    for (let i = 0; i < samples; i++) {
        let sum = 0;
        const start = i * blockSize;
        for (let j = 0; j < blockSize; j++) {
            const s = channelData[start + j] || 0;
            sum += Math.abs(s);
        }
        waveform.push(sum / blockSize);
    }
    return waveform;
};
</script>

