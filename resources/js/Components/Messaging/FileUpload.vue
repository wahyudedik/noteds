<template>
    <div class="relative">
        <input
            ref="fileInput"
            type="file"
            multiple
            :accept="accept"
            @change="handleFileSelect"
            class="hidden"
        />
        <button
            @click="$refs.fileInput.click()"
            class="p-2 text-gray-500 hover:text-gray-700"
        >
            📎
        </button>
    </div>
</template>

<script setup>
import { ref } from 'vue';

defineProps({
    accept: {
        type: String,
        default: 'image/*,application/pdf,.doc,.docx,audio/*',
    },
});

const emit = defineEmits(['files-selected']);

const fileInput = ref(null);

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);
    emit('files-selected', files);
    // Reset input
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};
</script>

